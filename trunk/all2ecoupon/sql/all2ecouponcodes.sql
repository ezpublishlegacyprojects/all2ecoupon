-- 
-- Tabellenstruktur für Tabelle `all2ecouponcodes`
-- 

CREATE TABLE `all2ecouponcodes` (
  `couponcode` varchar(20) NOT NULL,
  `informationcollection_id` int(11) NOT NULL,
  `contentobject_id` int(11) NOT NULL,
  PRIMARY KEY  (`couponcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;