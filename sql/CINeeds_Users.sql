USE CINeeds;

INSERT INTO CIN_User (email, username, password, admin) VALUES 
("john@myci.csuci.edu", "John Doe", "1234", FALSE),
("mary@myci.csuci.edu", "Mary Jane", "QWERTY", FALSE),
("bob@myci.csuci.edu", "Bob Robert", "password1", FALSE),
("admin@myci.csuci.edu", "Joe Admin", "admin1", TRUE);

SELECT * FROM CIN_User;