<?php

    // copy this file to secrets.php, delete the next two lines, and fill in the values
    header('HTTP/1.0 403 Forbidden');
    die;

    // page cannot be accessed directly
    if (!defined('AllowedDirectAccess')) {
        header('HTTP/1.0 403 Forbidden');
        die;
    }

    var $joshuaProjectApiKey='';
    var $mailgunApiKey='';
    var $mailgunSendingApiKey='';
    var $mailgunDomain='mg.pray.support';
    var $mailingList='daily%40'+$mailgunDomain;
    var $mailgunBaseUrl='https://api.mailgun.net/v3';
    var $mailgunAddListMemberUrl=$mailgunBaseUrl+'/lists/'+$mailgunBaseUrl+'/members'; # POST; address: string, name?: string, subscribed?: boolean, upsert?: boolean
    var $mailgunSendMailUrl=$mailgunBaseUrl+'/'+$mailgunDomain+'/messages'; # POST; (multipart/form-data) from: string, to:: string, subject: string, cc?: string[], bcc?: string[], text?: string, html?: string, template?: string, t:text?: string, t:variables?: string

?>