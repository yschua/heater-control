BEGIN TRANSACTION;
DROP TABLE IF EXISTS `schedule`;
CREATE TABLE IF NOT EXISTS `schedule` (
	`schedule_id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`day_id`	INTEGER NOT NULL,
	`start_time`	TEXT NOT NULL,
	`end_time`	TEXT NOT NULL,
	`mode`	TEXT,
	`is_enable`	INTEGER NOT NULL DEFAULT 1,
	`is_active`	INTEGER NOT NULL DEFAULT 0,
	FOREIGN KEY(`day_id`) REFERENCES `day`(`day_id`)
);
DROP TABLE IF EXISTS `on_off_cycle`;
CREATE TABLE IF NOT EXISTS `on_off_cycle` (
	`is_active`	INTEGER NOT NULL DEFAULT 0,
	`on_duration`	INTEGER NOT NULL DEFAULT 0,
	`off_duration`	INTEGER NOT NULL DEFAULT 0,
	`last_cycle`	TEXT
);
INSERT INTO `on_off_cycle` (is_active,on_duration,off_duration,last_cycle) VALUES (0,20,10,NULL);
DROP TABLE IF EXISTS `heater`;
CREATE TABLE IF NOT EXISTS `heater` (
	`heater_id`	INTEGER,
	`selected_power`	INTEGER NOT NULL DEFAULT 0,
	`current_power`	INTEGER NOT NULL DEFAULT 0,
	`selected_temperature`	REAL NOT NULL DEFAULT 15.0,
	`current_temperature`	REAL NOT NULL DEFAULT 15.0,
	`timeout`	TEXT,
	`is_on`	INTEGER NOT NULL DEFAULT 0,
	PRIMARY KEY(`heater_id`)
);
INSERT INTO `heater` (heater_id,selected_power,current_power,selected_temperature,current_temperature,timeout,is_on) VALUES (1,0,0,15.0,15.0,NULL,0);
DROP TABLE IF EXISTS `day`;
CREATE TABLE IF NOT EXISTS `day` (
	`day_id`	INTEGER NOT NULL,
	`name`	TEXT NOT NULL,
	`dop`	INTEGER NOT NULL,
	PRIMARY KEY(`day_id`)
);
INSERT INTO `day` (day_id,name,dop) VALUES (1,'Daily',127),
 (2,'Weekdays',31),
 (3,'Weekend',96),
 (4,'Monday',1),
 (5,'Tuesday',2),
 (6,'Wednesday',4),
 (7,'Thursday',8),
 (8,'Friday',16),
 (9,'Saturday',32),
 (10,'Sunday',64);
COMMIT;
