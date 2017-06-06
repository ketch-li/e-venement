<?php use_helper('CrossAppLink') ?>
<?php use_javascript('jquery', 'first') ?>
<?php $tokenService = $sf_context->getContainer()->get('pub_service') ?>

<div id="confirm-transactions">

<div class="ui-widget-header ui-corner-all">
    <h1><?php echo __('List of transactions to be confirmed by email') ?>Liste des opérations à confirmer par email</h1>
</div>

<div id="transactions_id" class="ui-widget-content ui-corner-all">
    <ul data-return-url="<?php echo url_for('transactionsList/index') ?>">
    <?php foreach ( $transactions as $tid ): ?>
        <li>
            <?php echo __('Transaction') ?>
            #<?php echo cross_app_link_to($tid, 'pub', 'transaction/sendEmail?id='.$tid.'&token='.$tokenService->getToken($tid)) ?>
            <span class="done"> ... <strong>done</strong></span>
        </li>
    <?php endforeach ?>
    </ul>
</div>

<script type="text/javascript"><!--
    LI.pubSendEmail = function(index) {
        if ( index === undefined ) {
            index = 0;
        }
        
        var emailLink = $($('#transactions_id a')[index]);
        if ( emailLink.length == 0 ) {
            window.location = $('#transactions_id ul').attr('data-return-url');
            return;
        }
        
        $.get(emailLink.prop('href'), function(){
            emailLink.siblings('.done').show();
            LI.pubSendEmail(++index);
        });
    }
    $(document).ready(function(){
        LI.pubSendEmail();
        //$('#transition').show();
    });
--></script>

</div>
