BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS `schedule` (
	`schedule_id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`days`	TEXT NOT NULL,
	`start_time`	TEXT NOT NULL,
	`end_time`	TEXT NOT NULL,
	`mode`	TEXT
);
CREATE TABLE IF NOT EXISTS `heater` (
	`heater_id`	INTEGER NOT NULL,
	`selected_power`	NUMERIC NOT NULL DEFAULT 0,
	`selected_temperature`	REAL NOT NULL DEFAULT 10.0,
	`current_power`	NUMERIC NOT NULL DEFAULT 0,
	`current_temperature`	REAL NOT NULL DEFAULT 10.0,
	`is_active`	NUMERIC NOT NULL DEFAULT 0,
	`timeout`	TEXT,
	`mode`	TEXT,
	PRIMARY KEY(`heater_id`)
);
INSERT INTO `heater` (heater_id,selected_power,selected_temperature,current_power,current_temperature,is_active,timeout,mode) VALUES (1,0,20.0,0,20.0,0,NULL,NULL);
COMMIT;
