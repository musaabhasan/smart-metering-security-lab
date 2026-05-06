<?php

declare(strict_types=1);

return [
    'portal' => [
        'slug' => 'smart-metering-security-lab',
        'title' => 'Smart Metering Security Lab',
        'tagline' => 'A research portal for smart-meter threat modeling, hardening evidence, secure logging, and utility synchronization assurance.',
    ],
    'paper' => [
        'title' => 'A Security Study for Smart Metering Systems',
        'authors' => ['Musaab Hasan', 'Farkhund Iqbal', 'Patrick C. K. Hung', 'Benjamin C. M. Fung', 'Laura Rafferty'],
        'date' => '2018-01-25',
        'publisher' => 'American Society of Civil Engineers',
        'repository' => 'eScholarship@McGill',
        'url' => 'https://escholarship.mcgill.ca/concern/articles/9c67wt37r',
        'keywords' => ['Security Design', 'Smart City', 'Smart Meter', 'Smart Grid', 'Smart Metering System'],
        'architecture_layers' => 5,
        'log_parameters' => 15,
        'monthly_power_estimate_kw' => 1.5,
        'major_threats' => 6,
    ],
    'layers' => [
        ['key' => 'sensors', 'label' => 'Electronic Sensors', 'description' => 'Measure current, voltage, power, and power factor across three-phase systems.', 'priority' => 1],
        ['key' => 'development_board', 'label' => 'Development Board', 'description' => 'Processes readings and hosts local services for logging and presentation.', 'priority' => 2],
        ['key' => 'consumption_logging', 'label' => 'Power Consumption Logging', 'description' => 'Writes structured measurement data into temporary logs and MySQL tables.', 'priority' => 3],
        ['key' => 'database_portal', 'label' => 'Database and Web Portal', 'description' => 'Stores consumption data and exposes user-facing PHP views for live and historical readings.', 'priority' => 4],
        ['key' => 'utility_sync', 'label' => 'Power Utility Synchronization', 'description' => 'Verifies, dumps, and securely transfers meter data to utility infrastructure.', 'priority' => 5],
    ],
    'threats' => [
        ['key' => 'energy_theft', 'label' => 'Energy Theft', 'severity' => 5, 'description' => 'Attempts to bypass measurement, tamper with terminals, or falsify delivered energy readings.'],
        ['key' => 'identity_spoofing', 'label' => 'Identity Spoofing', 'severity' => 4, 'description' => 'Use of forged meter identities to transmit misleading data or shift consumption costs.'],
        ['key' => 'denial_of_service', 'label' => 'Denial of Service', 'severity' => 5, 'description' => 'Flooding or availability attacks against meters, gateways, or utility endpoints.'],
        ['key' => 'sniffing_traffic_analysis', 'label' => 'Sniffing and Traffic Analysis', 'severity' => 4, 'description' => 'Interception of usage traffic to infer private consumption patterns or alter communications.'],
        ['key' => 'malware_spreading', 'label' => 'Malware Spreading', 'severity' => 5, 'description' => 'Propagation of meter malware across similar devices or grid-connected networks.'],
        ['key' => 'data_tampering', 'label' => 'Data Tampering', 'severity' => 4, 'description' => 'Unauthorized changes to logs, meter readings, portal records, or utility transfer files.'],
    ],
    'controls' => [
        ['key' => 'dual_channel_measurement', 'label' => 'Dual-Channel Measurement', 'family' => 'measurement', 'weight' => 9, 'threats' => ['energy_theft'], 'description' => 'Measure live and neutral channels and compare readings for tamper indicators.'],
        ['key' => 'tamper_behavior_analytics', 'label' => 'Tamper Behavior Analytics', 'family' => 'measurement', 'weight' => 8, 'threats' => ['energy_theft', 'data_tampering'], 'description' => 'Detect abnormal measurement patterns and suspicious changes in usage behavior.'],
        ['key' => 'strong_meter_identity', 'label' => 'Strong Meter Identity', 'family' => 'identity', 'weight' => 9, 'threats' => ['identity_spoofing'], 'description' => 'Bind meter identity to authenticated device credentials and server-side validation.'],
        ['key' => 'modern_network_transport', 'label' => 'Modern Network Transport', 'family' => 'communication', 'weight' => 7, 'threats' => ['identity_spoofing', 'sniffing_traffic_analysis'], 'description' => 'Avoid legacy GSM/SMS communication paths and use stronger supported transport channels.'],
        ['key' => 'tls_portal_access', 'label' => 'TLS Portal Access', 'family' => 'communication', 'weight' => 8, 'threats' => ['sniffing_traffic_analysis', 'data_tampering'], 'description' => 'Protect the consumer-facing web portal with strong TLS configuration.'],
        ['key' => 'sftp_utility_transfer', 'label' => 'SFTP Utility Transfer', 'family' => 'utility_sync', 'weight' => 8, 'threats' => ['sniffing_traffic_analysis', 'data_tampering'], 'description' => 'Transfer database dumps and utility files through secure file transfer.'],
        ['key' => 'database_integrity_check', 'label' => 'Database Integrity Check', 'family' => 'utility_sync', 'weight' => 8, 'threats' => ['data_tampering'], 'description' => 'Check database integrity before exporting meter data to utility infrastructure.'],
        ['key' => 'audit_logging', 'label' => 'Audit Logging', 'family' => 'monitoring', 'weight' => 7, 'threats' => ['denial_of_service', 'data_tampering', 'malware_spreading'], 'description' => 'Collect failed logins, abnormal meter behavior, and portal activity for review.'],
        ['key' => 'packet_filtering', 'label' => 'Packet Filtering', 'family' => 'network', 'weight' => 8, 'threats' => ['denial_of_service', 'malware_spreading'], 'description' => 'Block unexpected ICMP, broadcast, and unapproved inbound or outbound traffic.'],
        ['key' => 'ips_rules', 'label' => 'Intrusion Protection Rules', 'family' => 'network', 'weight' => 8, 'threats' => ['denial_of_service', 'malware_spreading'], 'description' => 'Use intrusion protection rules to detect and block unusual traffic.'],
        ['key' => 'service_minimization', 'label' => 'Service Minimization', 'family' => 'hardening', 'weight' => 7, 'threats' => ['malware_spreading', 'denial_of_service'], 'description' => 'Remove unneeded packages and disable nonessential services.'],
        ['key' => 'usb_restriction', 'label' => 'USB Restriction', 'family' => 'hardening', 'weight' => 6, 'threats' => ['malware_spreading', 'data_tampering'], 'description' => 'Disable or control USB devices on the meter platform.'],
        ['key' => 'periodic_security_updates', 'label' => 'Periodic Security Updates', 'family' => 'lifecycle', 'weight' => 9, 'threats' => ['malware_spreading', 'denial_of_service', 'data_tampering'], 'description' => 'Maintain a controlled process for applying security updates to deployed meters.'],
    ],
    'assessment_dimensions' => [
        'measurement' => 'Measurement assurance',
        'identity' => 'Device identity',
        'communication' => 'Secure communication',
        'utility_sync' => 'Utility synchronization',
        'monitoring' => 'Monitoring and audit',
        'network' => 'Network protection',
        'hardening' => 'Platform hardening',
        'lifecycle' => 'Security lifecycle',
    ],
];
