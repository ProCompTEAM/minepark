INSERT 
	INTO phones (Subject, Number, SubjectType, CreatedDate, UpdatedDate)  
	VALUES(
		(SELECT Name FROM users AS Subject) is not null,
		((SELECT Id FROM users AS NUMBER) is not NULL) + 1000,
		1,
		CURRENT_DATE(),
		CURRENT_DATE()
	)