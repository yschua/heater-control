BEGIN TRANSACTION;
CREATE TABLE "heater" (
	`heater_id`	INTEGER,
	`selected_power`	NUMERIC NOT NULL DEFAULT 0,
	`selected_temperature`	REAL NOT NULL DEFAULT 10.0,
	`current_power`	NUMERIC NOT NULL DEFAULT 0,
	`current_temperature`	REAL NOT NULL DEFAULT 10.0,
	`is_active`	NUMERIC NOT NULL DEFAULT 0,
	PRIMARY KEY(heater_id)
);
INSERT INTO `heater` (heater_id,selected_power,selected_temperature,current_power,current_temperature,is_active) VALUES (1,0,17.0,0,17.0,0);
COMMIT;
