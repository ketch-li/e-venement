<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2016 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2016 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    sfApplicationConfiguration::getActive()->loadHelpers(array('I18N'));

    $cpt = 0;
    $max = array(
      'print'     => sfConfig::get('app_tickets_simplified_printing', false) && sfConfig::get('app_tickets_merge') ? 300 : 150,
      'duplicate' => 30,
    );
    
    $i = 0;

    $q = Doctrine::getTable('Transaction')
      ->createQuery('t')
      ->andWhere('tck.id NOT IN (SELECT tck2.duplicating FROM Ticket tck2 WHERE tck2.duplicating IS NOT NULL)')
      ->andWhere('tck.price_id IS NOT NULL')
      ->leftJoin('m.Location l')
      ->leftJoin('m.Organizers o')
      ->leftJoin('m.Event e')
      ->leftJoin('e.MetaEvent me')
      ->leftJoin('e.Companies c')
      ->leftJoin('tck.Gauge g')
      ->leftJoin('g.Workspace ws')
      ->orderBy('m.happens_at, tck.price_name, tck.id')
    ;
    $this->ids = array();
    if ( $request->getParameter('id', false) )
      $this->ids[] = $request->getParameter('id');
    if ( $request->getParameter('ids', false) )
    foreach ( explode('-',$request->getParameter('ids')) as $id )
      $this->ids[] = $id;
    $q->andWhereIn('t.id',$this->ids);

    // partial printing
    $this->toprint = array();
    if ( $tids = $request->getParameter('toprint',array()) )
    {
      if ( !is_array($tids) ) $tickets = array($tids);
      foreach ( $tids as $key => $value )
        $tids[$key] = intval($value);

      $q->andWhereIn('tck.id',$tids);
    }

    // improving the speed reducing the fist set of tickets
    if ( $request->getParameter('price_name', false) )
      $q->andWhere('tck.price_name ILIKE ?', $request->getParameter('price_name'));
    if ( $request->getParameter('manifestation_id', false) )
      $q->andWhere('tck.manifestation_id = ?', $request->getParameter('manifestation_id'));

    $this->transactions = $q->execute();
    $this->manifestation_id = $request->getParameter('manifestation_id');

    // if any ticket needs a seat, do what's needed
    foreach ( $this->transactions as $this->transaction )
      $this->redirectToSeatsAllocationIfNeeded('print');

    $fingerprint = NULL;
    $this->print_again = false;
    $this->grouped_tickets = false;
    $this->duplicate = $request->getParameter('duplicate') == 'true' && $this->getUser()->hasCredential('tck-duplicate-ticket');
    $this->tickets = array();
    $update = array('printed_at' => array(), 'integrated_at' => array());

    // grouped tickets
    if ( sfConfig::get('app_tickets_authorize_grouped_tickets', false)
      && $request->hasParameter('grouped_tickets') )
    {
      $fingerprint = date('YmdHis').'-'.$this->getUser()->getId();
      $this->grouped_tickets = true;

      foreach ( $this->transactions as $transaction )
      foreach ( $transaction->Tickets as $ticket )
      {
        try {
          // member cards (cf. PluginTicket::preUpdate())
          if ( $ticket->Price->member_card_linked )
            throw new liEvenementException('It is forbidden to group tickets linked with a member card');

          // duplicates
          if ( $request->getParameter('duplicate') == 'true' )
          {
            if ( $cpt >= $max['duplicate'] ) // duplicating is MUCH longer than simple printing
            {
              $this->toprint[] = $ticket->id;
              $this->print_again = true;
            }
            elseif ( strcasecmp(trim($ticket->price_name),trim($request->getParameter('price_name'))) == 0
              && $ticket->printed_at
              && !($request->getParameter('manifestation_id') && $ticket->manifestation_id != $request->getParameter('manifestation_id')) )
            {
              $cpt++;
              $newticket = $ticket->copy();
              $ticket->seat_id = NULL;
              $newticket->sf_guard_user_id = NULL;
              $newticket->created_at = NULL;
              $newticket->updated_at = NULL;
              $newticket->printed_at = date('Y-m-d H:i:s');
              $newticket->grouping_fingerprint = $fingerprint;
              $newticket->Duplicated = $ticket;
              $newticket->qrcode();
              $newticket->save();
              if ( $newticket->seat_id )
                $ticket->save();

              if ( isset($this->tickets[$id = $ticket->gauge_id.'-'.$ticket->price_id.'-'.$ticket->transaction_id]) )
              {
                $this->tickets[$id]['ticket'] = $newticket;
                $this->tickets[$id]['nb']++;
              }
              else
                $this->tickets[$id] = array('nb' => 1, 'ticket' => $newticket);
            }
          }

          else // not duplicates
          if ( !$ticket->printed_at && !$ticket->integrated_at
            && !($request->getParameter('manifestation_id') && $ticket->manifestation_id != $request->getParameter('manifestation_id')) )
          {
            if ( $cpt >= $max['print'] )
            {
              $this->print_again = true;
              break;
            }
            $cpt++;

            if ( $ticket->Manifestation->no_print )
              $update['integrated_at'][$ticket->id] = $ticket->id;
            else
            {
              $update['printed_at'][$ticket->id] = $ticket->id;

              if ( isset($this->tickets[$id = $ticket->gauge_id.'-'.$ticket->price_id.'-'.$ticket->transaction_id]) )
              {
                $this->tickets[$id]['ticket'] = $ticket; // adding a new one not saved
                $this->tickets[$id]['nb']++;
              }
              else // first ticket of the chain
                $this->tickets[$id] = array('nb' => 1, 'ticket' => $ticket);
            }
          }
        }
        catch ( liEvenementException $e )
        { error_log('An error occurred during grouped tickets #'.$ticket->id.' printing: '.$e->getMessage()); }
      }

      if ( $request->getParameter('duplicate') != 'true' )
      foreach ( $this->tickets as $ticket )
        $update['printed_at'][$ticket['ticket']->id] = $ticket['ticket']->id;
    }

    // normal / not grouped tickets
    else
    {
      foreach ( $this->transactions as $transaction )
      foreach ( $transaction->Tickets as $ticket )
      {
        try {
          // duplicates
          if ( $request->getParameter('duplicate') == 'true' )
          {
            if ( $cpt >= $max['duplicate'] ) // duplicating is MUCH longer than simple printing
            {
              $this->toprint[] = $ticket->id;
              $this->print_again = true;
            }
            elseif ( strcasecmp(trim($ticket->price_name),trim($request->getParameter('price_name'))) == 0
              && ($ticket->printed_at || $ticket->integrated_at)
              && !($request->getParameter('manifestation_id') && $ticket->manifestation_id != $request->getParameter('manifestation_id')) )
            {
              $cpt++;
              $newticket = $ticket->copy();
              $ticket->Transaction->Tickets[] = $newticket;
              $ticket->seat_id = NULL;
              $newticket->sf_guard_user_id = NULL;
              $newticket->created_at = NULL;
              $newticket->updated_at = NULL;
              $newticket->printed_at = date('Y-m-d H:i:s');
              $newticket->integrated_at = NULL;
              $newticket->Duplicated = $ticket;
              $newticket->qrcode;
              $newticket->save();
              if ( $newticket->seat_id )
                $ticket->save();

              $this->tickets[] = $newticket;
            }
          }

          else // $this->duplicate == false
          {
            if ( $cpt >= $max['print'] ) // duplicating is MUCH longer than simple printing
            {
              $this->print_again = true;
              break;
            }

            if ( sfConfig::get('app_tickets_simplified_printing', false) ||
                 sfConfig::get('app_tickets_dematerialized_thermic_printing', false) ||
              (
                !$ticket->printed_at && !$ticket->integrated_at
                && !($request->getParameter('manifestation_id') && $ticket->manifestation_id != $request->getParameter('manifestation_id'))
              )
            )
            {
              $cpt++;
              if ( $ticket->Manifestation->no_print
                || sfConfig::get('app_tickets_simplified_printing', false)
                || sfConfig::get('app_tickets_dematerialized_thermic_printing', false) )
              {
                // member cards (cf. PluginTicket::preUpdate()) OR auto controled tickets
                if ( $ticket->Price->member_card_linked || $ticket->Manifestation->Location->auto_control )
                {
                  $cpt += 2; // because member cards treatments take a loong time
                  $ticket->integrated_at = date('Y-m-d H:i:s');
                  $ticket->vat = $ticket->Manifestation->Vat->value;
                  //$ticket->qrcode;
                  $ticket->save();
                  $cpt += 2; // because member cards treatments take a loong time
                }
                else
                {
                  $update['integrated_at'][$ticket->id] = $ticket->id;
                }
                
                if ( sfConfig::get('app_tickets_simplified_printing', false) || sfConfig::get('app_tickets_dematerialized_thermic_printing', false) )
                  $this->tickets[] = $ticket;
              }
              else
              {
                // member cards (cf. PluginTicket::preUpdate()) OR auto controled tickets
                if ( $ticket->Price->member_card_linked || $ticket->Manifestation->Location->auto_control )
                {
                  $this->getUser()->setFlash('notice', __('The contact in the current transaction doesn\'t have the required member card type for price \'') . $ticket->Price->name . ('\''));
                  $cpt += 2; // because member cards treatments take a loong time
                  $ticket->printed_at = date('Y-m-d H:i:s');
                  $ticket->vat = $ticket->Manifestation->Vat->value;
                  $ticket->qrcode;
                  $ticket->save();
                  $cpt += 2; // because member cards treatments take a loong time
                }
                else{
                  $this->getUser()->setFlash('notice', 'member_card_linked_else');
                  $update['printed_at'][$ticket->id] = $ticket->id;
                }

                $this->tickets[] = $ticket;
              }
            }
          }
        }
        catch ( liEvenementException $e )
        { error_log('An error occurred during ticket #'.$ticket->id.' printing: '.$e->getMessage()); }
      }
    }

    // bulk updates
    foreach ( $update as $type => $ids )
    if ( count($ids) > 0 )
    {
      $q = Doctrine_Query::create()->update('Ticket t')
        ->whereIn('t.id',$ids)
        ->set('t.'.$type,'NOW()')
        ->set('t.updated_at','NOW()')
        ->set('t.vat', '(SELECT v.value FROM Manifestation m LEFT JOIN Vat v ON v.id = m .vat_id WHERE m.id = manifestation_id)')
        ->set('t.sf_guard_user_id',$this->getUser()->getId())
        ->set('t.version','t.version + 1')
        ->set('t.barcode',"md5('#'||id||'-".sfConfig::get('project_eticketting_salt', '')."')") // cf. Ticket::getBarcodePng()
      ;
      if ( !sfConfig::get('app_tickets_simplified_printing', false)
        && !sfConfig::get('app_tickets_dematerialized_thermic_printing', false) )
        $q->andWhere(sprintf('t.%s IS NULL',$type));

      // bulk update for grouped tickets
      if ( sfConfig::has('app_tickets_authorize_grouped_tickets')
        && sfConfig::get('app_tickets_authorize_grouped_tickets')
        && $request->hasParameter('grouped_tickets') )
      {
        if ( is_null($fingerprint) )
          throw new liEvenementException('Printing grouped tickets without a fingerprint is forbidden');

        $q->set('t.grouping_fingerprint',"'".$fingerprint."'");
      }
      
      $q->execute();

      // ticket version
      $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
      $query = 'INSERT INTO ticket_version SELECT * FROM ticket WHERE id IN ('.implode(',',$ids).')';
      $stmt = $pdo->prepare($query);
      $stmt->execute();
    }

    if ( count($this->tickets) <= 0 )
    {
      $this->setTemplate('close');
    }
    else
    {
      if ( sfConfig::get('app_tickets_id') != 'othercode' && !$request->hasParameter('rfid') )
        $this->setLayout('empty');
      else
      {
        $this->form = new BaseForm;

        foreach ( $this->tickets as $ticket )
        {
          $w = new sfWidgetFormInputText();
          $w->setLabel($ticket->Manifestation.' '.$ticket->price_name);
          $this->form->setWidget('['.$ticket->id.'][othercode]',$w);
        }
        $this->form->getWidgetSchema()->setNameFormat('ticket%s');

        $this->setTemplate('rfid');
      }
    }

    foreach ( $this->transactions as $transaction )
    $this->dispatcher->notify(new sfEvent($this, 'tck.tickets_print', array(
      'transaction' => $transaction,
      'tickets'     => $this->tickets,
      'duplicate'   => $this->duplicate,
      'user'        => $this->getUser(),
    )));

    if ( $request->hasParameter('direct') )
    {
      if ( count($this->tickets) <= 0 )
        return sfView::NONE;

      $this->setLayout(false);
      $this->getResponse()->setContentType('application/octet-stream');

      $usb = array_merge(sfConfig::get('software_internals_usb', array()), sfConfig::get('project_internals_usb', array()));
      $usbid = json_decode($request->getParameter('direct', false), true);
      $found = false;
      foreach ( $usb['printers'] as $type => $ids )
      if ( in_array($usbid, $ids) )
      {
        if ( sfConfig::get('sf_web_debug', false) )
          error_log(sprintf('Printing tickets: the given USB device does match our configuration (vid: %s, pid: %s).', $usbid['vid'], $usbid['pid']));
        $found = true;
        break;
      }
      if ( !$found && sfConfig::get('sf_web_debug', false) )
        error_log(sprintf('Printing tickets: ERROR the given USB device does not match our configuration (vid: %s, pid: %s).', $usbid['vid'], $usbid['pid']));

      // we need to have /usr/sbin/cupsfilter & /usr/bin/base64 installed to be able to use direct printing
      $paths = sfConfig::get('project_internals_exec_path', sfConfig::get('software_internals_exec_path'));
      if (!( isset($paths['cupsfilter']) && is_executable($paths['cupsfilter']) && isset($paths['base64']) && is_executable($paths['base64']) ))
      {
        error_log('Printing tickets: the workstation is ready for direct printing, but the server is not... Please correct this issue checking cupsfilter & base64 executables.');
        $found = false;
      }

      if ( !$found )
      {
        sfConfig::set('sf_web_debug', false);
        $this->getResponse()->setContentType('application/pdf');
        $this->printer = false;
      }
      else
        $this->printer = $type;

      return 'Direct';
    }

    if (!( sfConfig::get('app_tickets_simplified_printing', false) && count($this->tickets) > 0 ))
      return 'Success';

    $this->content = '';
    foreach ( $this->transactions as $transaction )
      $this->content .= $transaction->renderSimplifiedTickets(array('only' => $this->tickets));
    if (!( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') || $this->getTemplate() == 'rfid' ))
    {
      sfConfig::set('sf_web_debug', false);
      $this->getResponse()->setContentType('application/pdf');
    }
    $this->setLayout(false);
    if ( $this->getTemplate() == 'rfid' )
    {
      $this->setLayout('layout');
      return 'Success';
    }
    return 'Simplified';
