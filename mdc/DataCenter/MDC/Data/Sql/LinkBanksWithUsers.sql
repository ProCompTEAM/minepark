INSERT 
	INTO bankaccounts (Name, UnitId, Cash, Debit, Credit, PaymentMethod, CreatedDate, UpdatedDate)  
		SELECT Name,
		"MilkyWay",
        0.00,
        0.00,
        0.00,
        1,
        CURRENT_DATE(),
        CURRENT_DATE()
	FROM users