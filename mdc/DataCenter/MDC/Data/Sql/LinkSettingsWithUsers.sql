INSERT 
  INTO usersettings (UnitId, Name, Licenses, Attributes, Organisation, World, X, Y, Z)  
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