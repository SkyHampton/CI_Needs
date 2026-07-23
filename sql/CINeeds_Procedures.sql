USE CINeeds;

-- =========================
-- BAN USER
-- =========================
DROP PROCEDURE IF EXISTS ban_user;
DELIMITER //
CREATE PROCEDURE ban_user (IN inUserID INT)
BEGIN
    UPDATE CIN_User 
    SET banned = TRUE 
    WHERE userID = inUserID;
END//
DELIMITER ;

-- =========================
-- DELETE USER
-- =========================
DROP PROCEDURE IF EXISTS delete_user;
DELIMITER //
CREATE PROCEDURE delete_user (IN inUserID INT)
BEGIN
    DELETE FROM CIN_Reply WHERE userID = inUserID;
    DELETE FROM CIN_Post WHERE userID = inUserID;
    DELETE FROM CIN_User WHERE userID = inUserID;
END//
DELIMITER ;

-- =========================
-- FULFILL POST
-- =========================
DROP PROCEDURE IF EXISTS fulfill_post;
DELIMITER //
CREATE PROCEDURE fulfill_post (IN inPostID INT)
BEGIN
    UPDATE CIN_Post 
    SET fulfilled = TRUE 
    WHERE postID = inPostID;
END//
DELIMITER ;

-- =========================
-- FLAG POST (LOG + COUNT)
-- =========================
DROP PROCEDURE IF EXISTS flag_post;
DELIMITER //
CREATE PROCEDURE flag_post (
    IN inPostID INT, 
    IN inUserID INT,
    IN inFlagReason VARCHAR(40), 
    IN inFlagComment TINYTEXT
)
BEGIN
    -- Insert into flag log table
    INSERT INTO CIN_Flag (postID, userID, flagReason, flagComment)
    VALUES (inPostID, inUserID, inFlagReason, inFlagComment);

    -- Increment flag count in post table
    UPDATE CIN_Post 
    SET flagCount = flagCount + 1 
    WHERE postID = inPostID;
END//
DELIMITER ;

-- =========================
-- MOVE POST TO GRAVEYARD
-- =========================
DROP PROCEDURE IF EXISTS graveyard_post;
DELIMITER //
CREATE PROCEDURE graveyard_post (
    IN inPostID INT, 
    IN inAdminID INT, 
    IN inReason VARCHAR(255)
)
BEGIN
    -- Copy post to graveyard
    INSERT INTO CIN_Graveyard (
        postID, adminID, userID, postType, category, postTitle, 
        postData, postDate, imagePath, contact, reason, deletedDate
    )
    SELECT 
        postID, inAdminID, userID, postType, category, postTitle, 
        postData, postDate, imagePath, contact, inReason, CURDATE()
    FROM CIN_Post
    WHERE postID = inPostID;

    -- Delete from main table
    DELETE FROM CIN_Post 
    WHERE postID = inPostID;
END//
DELIMITER ;

-- =========================
-- TEST QUERIES (OPTIONAL)
-- =========================
-- SELECT * FROM CIN_User;
-- CALL ban_user(1);

-- SELECT * FROM CIN_Post;
-- CALL fulfill_post(1);

-- SELECT * FROM CIN_Flag;
-- CALL flag_post(2, 'Spam', 'Test comment');

-- CALL graveyard_post(1, 1, 'Violation of rules');

-- CALL delete_user(1); 