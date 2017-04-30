BEGIN TRANSACTION;
CREATE TABLE "heater" (
	`heater_id`	INTEGER,
	`power`	NUMERIC NOT NULL DEFAULT 0,
	`temperature`	REAL NOT NULL DEFAULT 10.0,
	`active_power`	NUMERIC NOT NULL DEFAULT 0,
	`active_temperature`	REAL NOT NULL DEFAULT 10.0,
	`is_online`	NUMERIC NOT NULL DEFAULT 0,
	PRIMARY KEY(heater_id)
);
INSERT INTO `heater` (heater_id,power,temperature,active_power,active_temperature,is_online) VALUES (1,0,17.0,0,17.0,0);
COMMIT;
