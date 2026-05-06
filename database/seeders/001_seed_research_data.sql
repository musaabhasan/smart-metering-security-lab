INSERT INTO architecture_layers (layer_key, label, description, priority_order) VALUES
('sensors', 'Electronic Sensors', 'Measure current, voltage, power, and power factor across three-phase systems.', 1),
('development_board', 'Development Board', 'Processes readings and hosts local services for logging and presentation.', 2),
('consumption_logging', 'Power Consumption Logging', 'Writes structured measurement data into temporary logs and MySQL tables.', 3),
('database_portal', 'Database and Web Portal', 'Stores consumption data and exposes user-facing PHP views for live and historical readings.', 4),
('utility_sync', 'Power Utility Synchronization', 'Verifies, dumps, and securely transfers meter data to utility infrastructure.', 5)
ON DUPLICATE KEY UPDATE label = VALUES(label), description = VALUES(description), priority_order = VALUES(priority_order);

INSERT INTO threat_catalog (threat_key, label, severity, description) VALUES
('energy_theft', 'Energy Theft', 5, 'Attempts to bypass measurement, tamper with terminals, or falsify delivered energy readings.'),
('identity_spoofing', 'Identity Spoofing', 4, 'Use of forged meter identities to transmit misleading data or shift consumption costs.'),
('denial_of_service', 'Denial of Service', 5, 'Flooding or availability attacks against meters, gateways, or utility endpoints.'),
('sniffing_traffic_analysis', 'Sniffing and Traffic Analysis', 4, 'Interception of usage traffic to infer private consumption patterns or alter communications.'),
('malware_spreading', 'Malware Spreading', 5, 'Propagation of meter malware across similar devices or grid-connected networks.'),
('data_tampering', 'Data Tampering', 4, 'Unauthorized changes to logs, meter readings, portal records, or utility transfer files.')
ON DUPLICATE KEY UPDATE label = VALUES(label), severity = VALUES(severity), description = VALUES(description);

INSERT INTO control_catalog (control_key, label, family, weight, threat_keys, description) VALUES
('dual_channel_measurement', 'Dual-Channel Measurement', 'measurement', 9, JSON_ARRAY('energy_theft'), 'Measure live and neutral channels and compare readings for tamper indicators.'),
('tamper_behavior_analytics', 'Tamper Behavior Analytics', 'measurement', 8, JSON_ARRAY('energy_theft','data_tampering'), 'Detect abnormal measurement patterns and suspicious changes in usage behavior.'),
('strong_meter_identity', 'Strong Meter Identity', 'identity', 9, JSON_ARRAY('identity_spoofing'), 'Bind meter identity to authenticated device credentials and server-side validation.'),
('modern_network_transport', 'Modern Network Transport', 'communication', 7, JSON_ARRAY('identity_spoofing','sniffing_traffic_analysis'), 'Avoid legacy GSM/SMS communication paths and use stronger supported transport channels.'),
('tls_portal_access', 'TLS Portal Access', 'communication', 8, JSON_ARRAY('sniffing_traffic_analysis','data_tampering'), 'Protect the consumer-facing web portal with strong TLS configuration.'),
('sftp_utility_transfer', 'SFTP Utility Transfer', 'utility_sync', 8, JSON_ARRAY('sniffing_traffic_analysis','data_tampering'), 'Transfer database dumps and utility files through secure file transfer.'),
('database_integrity_check', 'Database Integrity Check', 'utility_sync', 8, JSON_ARRAY('data_tampering'), 'Check database integrity before exporting meter data to utility infrastructure.'),
('audit_logging', 'Audit Logging', 'monitoring', 7, JSON_ARRAY('denial_of_service','data_tampering','malware_spreading'), 'Collect failed logins, abnormal meter behavior, and portal activity for review.'),
('packet_filtering', 'Packet Filtering', 'network', 8, JSON_ARRAY('denial_of_service','malware_spreading'), 'Block unexpected ICMP, broadcast, and unapproved inbound or outbound traffic.'),
('ips_rules', 'Intrusion Protection Rules', 'network', 8, JSON_ARRAY('denial_of_service','malware_spreading'), 'Use intrusion protection rules to detect and block unusual traffic.'),
('service_minimization', 'Service Minimization', 'hardening', 7, JSON_ARRAY('malware_spreading','denial_of_service'), 'Remove unneeded packages and disable nonessential services.'),
('usb_restriction', 'USB Restriction', 'hardening', 6, JSON_ARRAY('malware_spreading','data_tampering'), 'Disable or control USB devices on the meter platform.'),
('periodic_security_updates', 'Periodic Security Updates', 'lifecycle', 9, JSON_ARRAY('malware_spreading','denial_of_service','data_tampering'), 'Maintain a controlled process for applying security updates to deployed meters.')
ON DUPLICATE KEY UPDATE label = VALUES(label), family = VALUES(family), weight = VALUES(weight), threat_keys = VALUES(threat_keys), description = VALUES(description);

INSERT INTO meter_readings (meter_id, reading_time, phase_a_voltage, phase_a_current, phase_a_power, phase_b_voltage, phase_b_current, phase_b_power, phase_c_voltage, phase_c_current, phase_c_power, power_factor, integrity_hash) VALUES
('SM-RESEARCH-001', '2026-01-01 00:00:00', 230.100, 4.120, 948.012, 229.800, 4.050, 930.690, 230.400, 4.080, 940.032, 0.960, SHA2('SM-RESEARCH-001|2026-01-01 00:00:00', 256)),
('SM-RESEARCH-001', '2026-01-01 01:00:00', 229.900, 3.880, 892.012, 230.200, 3.920, 902.384, 230.000, 3.900, 897.000, 0.958, SHA2('SM-RESEARCH-001|2026-01-01 01:00:00', 256)),
('SM-RESEARCH-001', '2026-01-01 02:00:00', 230.300, 3.640, 838.292, 230.000, 3.610, 830.300, 229.700, 3.670, 843.999, 0.961, SHA2('SM-RESEARCH-001|2026-01-01 02:00:00', 256));

INSERT INTO experiments (title, objective, layer_count, threat_count, control_count, status) VALUES
('Smart metering security baseline', 'Represent the paper workflow: common threats, five architecture layers, structured consumption logging, and system hardening controls.', 5, 6, 13, 'completed'),
('Field hardening evidence collection', 'Collect real device package, service, firewall, transfer, and logging evidence for assurance review.', 5, 6, 13, 'planned'),
('Utility synchronization assurance', 'Add signed database export, transfer attestation, and utility-side receipt validation.', 5, 6, 13, 'planned')
ON DUPLICATE KEY UPDATE objective = VALUES(objective), layer_count = VALUES(layer_count), threat_count = VALUES(threat_count), control_count = VALUES(control_count), status = VALUES(status);
