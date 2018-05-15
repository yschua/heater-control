BEGIN TRANSACTION;
DROP TABLE IF EXISTS `schedule`;
CREATE TABLE IF NOT EXISTS `schedule` (
	`schedule_id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`day_id`	INTEGER NOT NULL,
	`start_time`	TEXT NOT NULL,
	`end_time`	TEXT NOT NULL,
	`mode`	TEXT,
	`is_enable`	INTEGER NOT NULL DEFAULT 1,
	FOREIGN KEY(`day_id`) REFERENCES `day`(`day_id`)
);
DROP TABLE IF EXISTS `heater`;
CREATE TABLE IF NOT EXISTS `heater` (
	`heater_id`	INTEGER,
	`selected_power`	INTEGER,
	`current_power`	INTEGER,
	`selected_temperature`	REAL,
	`current_temperature`	REAL,
	`is_active`	INTEGER,
	`timeout`	TEXT,
	`mode`	TEXT,
	PRIMARY KEY(`heater_id`)
);
INSERT INTO `heater` (heater_id,selected_power,current_power,selected_temperature,current_temperature,is_active,timeout,mode) VALUES (1,0,0,15.0,15.0,0,NULL,NULL);
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
