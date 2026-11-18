<?php

return [

    'table' => 'quebec_audit_logs',

    /*
     * Store the IP address and user agent of the request with each entry.
     * They are personal data: they stay outside the hash chain so they can be anonymized.
     */
    'store_request' => true,

    /*
     * Log every anonymization (DSAR erasure, retention...) announced by laravel-quebec's
     * PersonalDataAnonymized event.
     */
    'log_anonymizations' => true,

];
