DELETE FROM usersettings;

INSERT 
  INTO usersettings (Name, UnitId, Licenses, Attributes, Organisation, World, X, Y, Z)
       SELECT Name,
       "MilkyWay",
       NULL,
       NULL,
       0,
       NULL,
       0.00,
       0.00,
       0.00
  FROM users