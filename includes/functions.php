<?php
function formatUserDate($datetime) {
    $timezone = $_COOKIE['user_timezone'] ?? 'UTC';
    
    try {
        $date = new DateTime($datetime, new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone($timezone));
        
        // Timezone → Abbreviation mapping (with DST support)
        $tzAbbrMap = [
            // Americas
            'America/New_York'      => $date->format('I') ? 'EDT' : 'EST',
            'America/Chicago'        => $date->format('I') ? 'CDT' : 'CST',
            'America/Denver'        => $date->format('I') ? 'MDT' : 'MST',
            'America/Los_Angeles'   => $date->format('I') ? 'PDT' : 'PST',
            'America/Sao_Paulo'     => $date->format('I') ? 'BRST' : 'BRT',
            
            // Europe
            'Europe/London'         => $date->format('I') ? 'BST' : 'GMT',
            'Europe/Berlin'         => $date->format('I') ? 'CEST' : 'CET',
            'Europe/Paris'         => $date->format('I') ? 'CEST' : 'CET',
            'Europe/Moscow'         => 'MSK',
            
            // Asia
            'Asia/Tokyo'           => 'JST',
            'Asia/Shanghai'        => 'CST',
            'Asia/Seoul'          => 'KST',
            'Asia/Kolkata'         => 'IST',
            'Asia/Dubai'           => 'GST',
            'Asia/Jerusalem'       => $date->format('I') ? 'IDT' : 'IST',
            'Asia/Bangkok'         => 'ICT',
            'Asia/Hong_Kong'       => 'HKT',
            'Asia/Singapore'       => 'SGT',
            
            // Australia/Oceania
            'Australia/Sydney'     => $date->format('I') ? 'AEDT' : 'AEST',
            'Australia/Melbourne'  => $date->format('I') ? 'AEDT' : 'AEST',
            'Pacific/Auckland'     => $date->format('I') ? 'NZDT' : 'NZST',
            
            // Africa
            'Africa/Cairo'         => 'EET',
            'Africa/Johannesburg'  => 'SAST',
            'Africa/Casablanca'    => 'WET',
            
            // Other
            'Asia/Riyadh'          => 'AST',
            'Asia/Tehran'          => 'IRST',
            'Asia/Karachi'        => 'PKT',
            'Asia/Dhaka'          => 'BST',
        ];
        
        $abbr = $tzAbbrMap[$timezone] ?? $date->format('T');  // Fallback to PHP's abbreviation
        
        return $date->format('F j, Y, g:i A') . " " . $abbr;
        
    } catch (Exception $e) {
        // Fallback if timezone conversion fails
        return date('F j, Y, g:i A', strtotime($datetime)) . " (UTC)";
    }
}
?>