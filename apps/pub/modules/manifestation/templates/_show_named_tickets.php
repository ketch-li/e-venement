<?php use_javascript('pub-named-tickets?'.date('Ymd')) ?>
<?php use_stylesheet('pub-named-tickets?'.date('Ymd')) ?>
<form
  action="<?php echo url_for('ticket/modNamedTickets?manifestation_id='.$manifestation->id
    .(isset($ticket) && $ticket->getRawValue() instanceof Ticket ? '&ticket_id='.$ticket->id : '')
    .(isset($transaction) && $transaction->getRawValue() instanceof Transaction ? '&transaction_id='.$transaction->id : '')
  ) ?>"
  method="<?php echo sfConfig::get('sf_web_debug', false) ? 'get' : 'post' ?>"
  class="named-tickets"
  <?php echo isset($ticket) && $ticket->getRawValue() instanceof Ticket ? 'id="ticket-'.$ticket->id.'"' : '' ?>
>
  <h3><?php echo __('Customize your seats') ?></h3>
  <div class="ticket sample">
    <h4>
      <span class="id"><input type="hidden" name="ticket[%%ticket_id%%][id]" value="" /></span>
      <span class="gauge_name"></span>
      <span class="value"></span>
      <span class="taxes"></span>
    </h4>
    <div class="price">
      <span class="seat_label"><?php if ( isset($display_mods) && !$display_mods ) echo __('Seat #') ?></span><span class="seat_name"></span>
      <!--<span class="price_name"><select <?php if (!( isset($display_mods) && $display_mods )): ?>disabled="disabled"<?php endif ?> name="ticket[%%ticket_id%%][price_id]"></select></span>-->
      <span class="price_name">
        <?php if (!( isset($display_mods) && $display_mods )): ?>
          <?php echo format_currency($ticket->value, '€') ?>
        <?php else: ?>
          <select name="ticket[%%ticket_id%%][price_id]"></select>
        <?php endif ?>
      </span>
      <?php if (!( isset($display_mods) && !$display_mods )): ?>
      <button value="true" class="delete" name="ticket[%%ticket_id%%][delete]" title="<?php echo __('Delete', null, 'sf_admin') ?>">X</button>
      <?php endif ?>
    </div>
    <div class="contact">
      <?php
        $contacts = array();
        $this_page = array();
        foreach ( $sf_user->getTransaction()->getTickets() as $ticket )
        if ( $ticket->manifestation_id == $manifestation->id )
        {
          if ( $ticket->contact_id && !isset($this_page[$ticket->contact_id]) )
            $this_page[$ticket->contact_id] = $ticket->DirectContact;
        }
        else
        {
          if ( $ticket->contact_id && !isset($contacts[$ticket->contact_id]) )
            $contacts[$ticket->contact_id] = $ticket->DirectContact;
        }
        foreach ( $this_page as $contact )
        if ( isset($contacts[$contact->id]) )
          unset($contacts[$contact->id]);
      ?>
      <?php if ( $contacts ): ?>
      <span class="cherry-pick">
        <select name="previous_id" onchange="javascript: return LI.pubNamedTicketsCherryPick(this);">
          <option value=""><?php echo __('Take back a previous contact') ?></option>
          <?php foreach ( $contacts as $contact ): ?>
            <option
              value="<?php echo $contact->id ?>"
              <?php foreach ( array('title', 'name', 'firstname', 'email') as $field ): ?>
                data-<?php echo $field ?>="<?php echo $contact->$field ?>"
              <?php endforeach ?>
            ><?php echo $contact ?> &lt;<?php echo $contact->email ?>&gt;</option>
          <?php endforeach ?>
        </select>
      </span>
      <?php endif ?>
      <span class="contact_id">
        <input class="id" type="hidden" value="" name="ticket[%%ticket_id%%][contact][id]" />
        <input class="force" type="hidden" value="" name="ticket[%%ticket_id%%][contact][force]" />
      </span>
      <span class="contact_title">
        <label><?php echo __('Title') ?>:</label>
        <select type="text" value="" name="ticket[%%ticket_id%%][contact][title]" title="<?php echo __('Title') ?>">
          <option value=""><?php echo __('Title') ?></option>
          <?php foreach ( Doctrine::getTable('TitleType')->createQuery('t')->execute() as $title ): ?>
          <option value="<?php echo $title ?>"><?php echo $title ?></option>
          <?php endforeach ?>
        </select>
      </span>
      <span class="contact_name">
        <label><?php echo __('Name') ?></label>
        <input type="text" value="" name="ticket[%%ticket_id%%][contact][name]" title="<?php echo __('Name') ?>" />
      </span>
      <span class="contact_firstname">
        <label><?php echo __('Firstname') ?></label>
        <input type="text" value="" name="ticket[%%ticket_id%%][contact][firstname]" title="<?php echo __('Firstname') ?>" />
      </span>
      <br/>
      <span class="contact_email">
        <label><?php echo __('Email address') ?></label>
        <input type="email" value="" name="ticket[%%ticket_id%%][contact][email]" title="<?php echo __('Email address') ?>" />
      </span>
      <button class="me" name="ticket[%%ticket_id%%][me]" value="<?php echo $sf_user->getTransaction()->contact_id ?>" title="<?php echo __('Give me this ticket') ?>"><?php echo __('My seat') ?></button>
      <br/>
      <span class="comment">
        <label><?php echo __('Any comment?') ?></label>
        <input type="text" value="" name="ticket[%%ticket_id%%][comment]" title="<?php echo __('Comment') ?>" maxlength="255" />
      </span>
    </div>
  </div>
</form>

<?php if ( sfConfig::get('app_tickets_always_need_a_contact',false) ): ?>
<p class="info">
  <?php echo __('You can take back a contact only once. In case of necessity, %%tagstart%%reload the page%%tagend%%...', array('%%tagstart%%' => '<a href="">', '%%tagend%%' => '</a>')) ?>
</p>
<?php endif ?>

<?php if (!( isset($display_continue) && !$display_continue )): ?>
<p class="submit">
  <a class="complete" href="<?php echo url_for('ticket/completeNamedTickets?manifestation_id='.$manifestation->id) ?>"><button name="complete" value="">
    <?php echo __('Resume contacts') ?>
  </button></a>
  <a href="<?php echo url_for('transaction/show?id='.$sf_user->getTransactionId()) ?>"><button name="submit" value="">
    <?php echo __('Continue') ?>
  </button></a>
</p>
<?php endif ?>
