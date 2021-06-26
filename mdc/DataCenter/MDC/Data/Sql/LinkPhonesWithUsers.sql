SET @CurrentIndex = 0;
	INSERT 
      INTO phones (Subject, Number, SubjectType, CreatedDate, UpdatedDate)  
	SELECT Name,
		   (@CurrentIndex := @CurrentIndex + 1) + 1000,
		   1,
		   CURRENT_DATE(),
		   CURRENT_DATE()
      FROM users