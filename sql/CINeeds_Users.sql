USE cineedsc_db;

INSERT INTO CIN_User (email, username, password) VALUES 
("john@myci.csuci.edu", "John Doe", "1234"),
("mary@myci.csuci.edu", "Mary Jane", "QWERTY"),
("bob@myci.csuci.edu", "Bob Robert", "password1");

SELECT * FROM CIN_User;