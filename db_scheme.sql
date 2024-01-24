-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
-- Additional information has been removed for security purposes

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: STUDEZY (You may choose any other name you want)
--

-- --------------------------------------------------------

DELIMITER $$
--
-- Procedures
--
CREATE PROCEDURE `add_asset` (IN `asset_id` VARCHAR(13), IN `asset_name` VARCHAR(60), IN `asset_type` VARCHAR(10), IN `oid` VARCHAR(13), IN `cid` VARCHAR(13), OUT `success` TINYINT(1))  BEGIN

SELECT MAX(i) INTO @maxindex FROM assets WHERE course=cid;

SELECT `name` INTO @exists FROM assets WHERE `name`=asset_name AND course=cid;

IF @exists IS NULL THEN
	INSERT INTO `assets`(`id`, `name`, `type`, `owner`, `course`, `i`, `last_edited`) VALUES (asset_id,asset_name,asset_type,oid,cid,IF(@maxindex IS NULL, 0, @maxindex + 1),CURRENT_DATE);
	SELECT 1 INTO success;
ELSE
	SELECT 0 INTO success;
END IF;

SELECT success;

END$$

CREATE PROCEDURE `add_chapter` (IN `id` VARCHAR(13), IN `chapter_name` VARCHAR(35), IN `course_id` VARCHAR(13), OUT `success` TINYINT(1))  BEGIN

SELECT MAX(nr) INTO @chap_max FROM chapters WHERE course=course_id;

SELECT `name` INTO @name_exists FROM chapters WHERE `name`=chapter_name;

IF @name_exists IS NULL THEN
    INSERT INTO `chapters`(`id`, `name`, `course`, `created`, `nr`) VALUES (id, chapter_name, course_id, CURRENT_DATE, IF(@chap_max IS NULL, 0, @chap_max + 1));
    SELECT 1 INTO success;
ELSE
	SELECT 0 INTO success;
END IF;

SELECT success;

END$$

CREATE PROCEDURE `change_chapter_order` (IN `course_id` VARCHAR(13), IN `chapter_order` VARCHAR(250))  BEGIN

DECLARE i SMALLINT(5);

SET @i = 0;

WHILE @i < JSON_LENGTH(chapter_order) DO
	SELECT JSON_UNQUOTE(JSON_EXTRACT(chapter_order, CONCAT('$[',@i,']'))) INTO @id;
    UPDATE `chapters` SET `nr` = @i WHERE id=@id;
    SELECT @i + 1 INTO @i;
END WHILE;

END$$

CREATE PROCEDURE `create_chat` (IN `name` VARCHAR(35), OUT `chid` VARCHAR(128))  BEGIN

    SET chid := UUID();
    INSERT INTO chats (`chat_id`, `name`, `created`) VALUES (chid, name, CURRENT_TIMESTAMP);
    SELECT chid;

END$$

CREATE PROCEDURE `create_user` (IN `uid` VARCHAR(13), IN `user_name` VARCHAR(25), IN `email` VARCHAR(25), IN `password` VARCHAR(62), IN `ic` VARCHAR(15), IN `vc` VARCHAR(6), OUT `success` BOOLEAN)  BEGIN

DECLARE validity DATE;
DECLARE un VARCHAR(25);
DECLARE mail_addr VARCHAR(25);

SELECT valid_until INTO @validity FROM invitation_codes WHERE code=ic;
SELECT uname INTO @un FROM users WHERE uname=user_name;
SELECT mail INTO @mail_addr FROM users WHERE mail=email;

IF DATE(@validity) >= DATE(CURRENT_DATE) AND @un IS NULL AND @mail_addr IS NULL THEN
INSERT INTO `users`(`uid`, `uname`, `mail`, `password`, `created`) VALUES (uid, user_name, email, password, CURRENT_DATE);
INSERT INTO confirmation_codes(uid, code) VALUES (uid, vc);
SET success = true;
ELSE SET success = false;
END IF;

SELECT success;

END$$

CREATE PROCEDURE `get_course` (IN `course` VARCHAR(13), IN `user` VARCHAR(13), IN `touch` BOOLEAN, OUT `id` VARCHAR(13), OUT `name` VARCHAR(25), OUT `description` VARCHAR(200), OUT `owner` VARCHAR(13), OUT `private` BOOLEAN, OUT `access_key` VARCHAR(60), OUT `subscribers` INT(5), OUT `last_opened` TIMESTAMP)  BEGIN

SELECT * INTO id, name, description, owner, private, access_key FROM `courses` WHERE courses.id=course;

SELECT COUNT(*) INTO subscribers FROM subscriptions WHERE cid=course;

SELECT subscriptions.last_used INTO last_opened FROM subscriptions WHERE subscriptions.uid=user AND subscriptions.cid=course;

IF touch = true THEN
UPDATE subscriptions SET last_used=CURRENT_TIMESTAMP WHERE uid=user AND cid=course;
END IF;

SELECT id, name, description, owner, private, access_key, subscribers, last_opened;

END$$

CREATE PROCEDURE `set_mail_verified` (IN `id` VARCHAR(13))  BEGIN

UPDATE users SET email_verified=1 WHERE uid=id;
DELETE FROM confirmation_codes WHERE uid=id;

END$$

CREATE PROCEDURE `subscribe` (IN `user` VARCHAR(13), IN `course` VARCHAR(13), OUT `success` BOOLEAN)  BEGIN

DECLARE existing VARCHAR(13);

SELECT uid INTO @existing FROM subscriptions WHERE uid=user AND cid=course;

IF @existing IS NULL THEN
INSERT INTO `subscriptions`(`uid`, `cid`, `joined`, `last_used`) VALUES (user, course, CURRENT_DATE, CURRENT_DATE);
SET success = true;
ELSE
SET success = false;
END IF;

SELECT success;

END$$

DELIMITER ;

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` varchar(13) NOT NULL,
  `title` varchar(40) NOT NULL,
  `content` varchar(250) NOT NULL,
  `user` varchar(13) NOT NULL,
  `course` varchar(13) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcement_comments`
--

CREATE TABLE `announcement_comments` (
  `id` varchar(13) NOT NULL,
  `content` varchar(250) NOT NULL,
  `announcement` varchar(13) NOT NULL,
  `user` varchar(13) NOT NULL,
  `time` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` varchar(13) NOT NULL,
  `name` varchar(60) NOT NULL,
  `type` varchar(10) NOT NULL,
  `owner` varchar(13) NOT NULL,
  `course` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `i` int NOT NULL,
  `last_edited` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` varchar(13) NOT NULL,
  `title` varchar(40) NOT NULL,
  `description` varchar(350) NOT NULL,
  `course` varchar(13) NOT NULL,
  `owner` varchar(13) NOT NULL,
  `created` timestamp NOT NULL,
  `due` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignment_feedback`
--

CREATE TABLE `assignment_feedback` (
  `id` varchar(13) NOT NULL,
  `content` varchar(750) NOT NULL,
  `recipient` varchar(13) NOT NULL,
  `sender` varchar(13) NOT NULL,
  `assignment` varchar(13) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignment_submissions`
--

CREATE TABLE `assignment_submissions` (
  `id` varchar(13) NOT NULL,
  `user` varchar(13) NOT NULL,
  `assignment` varchar(13) NOT NULL,
  `submitted` timestamp NOT NULL,
  `fileName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `type` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendars`
--

CREATE TABLE `calendars` (
  `id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_entries`
--

CREATE TABLE `calendar_entries` (
  `id` varchar(13) NOT NULL,
  `calendar_id` varchar(13) NOT NULL,
  `owner` varchar(13) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(250) NOT NULL,
  `time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `private` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_subscriptions`
--

CREATE TABLE `calendar_subscriptions` (
  `child` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `parent` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chapters`
--

CREATE TABLE `chapters` (
  `id` varchar(13) NOT NULL,
  `name` varchar(35) NOT NULL,
  `course` varchar(13) NOT NULL,
  `created` date NOT NULL,
  `nr` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chapter_progress`
--

CREATE TABLE `chapter_progress` (
  `chapter` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `user` varchar(13) NOT NULL,
  `stars` int NOT NULL,
  `progress` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chats`
--

CREATE TABLE `chats` (
  `chat_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(40) DEFAULT NULL,
  `created` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_memberships`
--

CREATE TABLE `chat_memberships` (
  `uid` varchar(13) NOT NULL,
  `chat_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `joined` timestamp NOT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `confirmation_codes`
--

CREATE TABLE `confirmation_codes` (
  `uid` varchar(13) NOT NULL,
  `code` varchar(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` varchar(13) NOT NULL,
  `name` varchar(25) NOT NULL,
  `description` varchar(200) NOT NULL,
  `owner` varchar(13) NOT NULL,
  `private` tinyint(1) NOT NULL,
  `access_key` varchar(60) NOT NULL,
  `calendar_id` varchar(13) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` varchar(13) NOT NULL,
  `name` varchar(35) NOT NULL,
  `last_edited` timestamp NOT NULL,
  `owner` varchar(13) NOT NULL,
  `course` varchar(13) DEFAULT NULL,
  `private` tinyint(1) NOT NULL DEFAULT '0',
  `description` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_permissions`
--

CREATE TABLE `document_permissions` (
  `doc_id` varchar(13) NOT NULL,
  `user` varchar(13) NOT NULL,
  `write_access` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doc_tokens`
--

CREATE TABLE `doc_tokens` (
  `uid` varchar(13) NOT NULL,
  `token` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `followings`
--

CREATE TABLE `followings` (
  `follower` varchar(13) NOT NULL,
  `following` varchar(13) NOT NULL,
  `follow_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invitation_codes`
--

CREATE TABLE `invitation_codes` (
  `code` varchar(15) NOT NULL,
  `valid_until` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `official_titles`
--

CREATE TABLE `official_titles` (
  `id` varchar(13) NOT NULL,
  `user` varchar(13) NOT NULL,
  `title_name` varchar(100) NOT NULL,
  `type` tinyint NOT NULL,
  `issuer` varchar(13) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `uid` varchar(13) NOT NULL,
  `cid` varchar(13) NOT NULL,
  `joined` date NOT NULL,
  `last_used` timestamp NOT NULL,
  `notify` tinyint(1) NOT NULL DEFAULT '0',
  `email_notify` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `surveys`
--

CREATE TABLE `surveys` (
  `id` varchar(13) NOT NULL,
  `owner` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `title` varchar(40) NOT NULL,
  `description` varchar(250) NOT NULL,
  `course` varchar(13) DEFAULT NULL,
  `show_answers` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `survey_answers`
--

CREATE TABLE `survey_answers` (
  `answerset_id` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `question` varchar(13) NOT NULL,
  `content` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `survey_answerset`
--

CREATE TABLE `survey_answerset` (
  `id` varchar(13) NOT NULL,
  `survey` varchar(13) NOT NULL,
  `submitted` timestamp NOT NULL,
  `user` varchar(13) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `survey_questions`
--

CREATE TABLE `survey_questions` (
  `id` varchar(13) NOT NULL,
  `survey` varchar(13) NOT NULL,
  `content` varchar(100) NOT NULL,
  `description` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `type` tinyint NOT NULL,
  `options` json DEFAULT NULL,
  `nr` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `title_issuers`
--

CREATE TABLE `title_issuers` (
  `id` varchar(13) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uid` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `uname` varchar(25) NOT NULL,
  `description` varchar(350) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `mail` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(62) NOT NULL,
  `created` date DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `last_session` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `message_token` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `calendar_id` varchar(13) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vocabularysets`
--

CREATE TABLE `vocabularysets` (
  `id` varchar(13) NOT NULL,
  `name` varchar(30) NOT NULL,
  `description` varchar(250) NOT NULL,
  `user` varchar(13) NOT NULL,
  `course` varchar(13) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vocabulary_scores`
--

CREATE TABLE `vocabulary_scores` (
  `word_id` varchar(13) NOT NULL,
  `user` varchar(13) NOT NULL,
  `score` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vocabulary_words`
--

CREATE TABLE `vocabulary_words` (
  `word_id` varchar(13) NOT NULL,
  `set_id` varchar(13) NOT NULL,
  `word` varchar(50) NOT NULL,
  `definition` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `user` (`user`),
  ADD KEY `course` (`course`);

--
-- Indexes for table `announcement_comments`
--
ALTER TABLE `announcement_comments`
  ADD KEY `announcement` (`announcement`),
  ADD KEY `user` (`user`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `id_2` (`id`),
  ADD KEY `owner` (`owner`),
  ADD KEY `course` (`course`) USING BTREE;

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD KEY `owner` (`owner`),
  ADD KEY `course` (`course`);

--
-- Indexes for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `id_2` (`id`);

--
-- Indexes for table `calendars`
--
ALTER TABLE `calendars`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `calendar_entries`
--
ALTER TABLE `calendar_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `calendar_id` (`calendar_id`),
  ADD KEY `time` (`time`);

--
-- Indexes for table `chapters`
--
ALTER TABLE `chapters`
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `course` (`course`);

--
-- Indexes for table `chapter_progress`
--
ALTER TABLE `chapter_progress`
  ADD KEY `chapter` (`chapter`),
  ADD KEY `user` (`user`);

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`chat_id`),
  ADD UNIQUE KEY `chat_id` (`chat_id`),
  ADD KEY `chat_id_2` (`chat_id`);

--
-- Indexes for table `chat_memberships`
--
ALTER TABLE `chat_memberships`
  ADD KEY `chat_id` (`chat_id`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `confirmation_codes`
--
ALTER TABLE `confirmation_codes`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `uid_2` (`uid`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `id_2` (`id`),
  ADD KEY `owner` (`owner`),
  ADD KEY `private` (`private`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `owner` (`owner`),
  ADD KEY `course` (`course`);

--
-- Indexes for table `document_permissions`
--
ALTER TABLE `document_permissions`
  ADD KEY `doc_id` (`doc_id`),
  ADD KEY `user` (`user`);

--
-- Indexes for table `doc_tokens`
--
ALTER TABLE `doc_tokens`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `uid` (`uid`);

--
-- Indexes for table `followings`
--
ALTER TABLE `followings`
  ADD KEY `follower` (`follower`),
  ADD KEY `following` (`following`);

--
-- Indexes for table `official_titles`
--
ALTER TABLE `official_titles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `user` (`user`),
  ADD KEY `issuer` (`issuer`),
  ADD KEY `id_2` (`id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD KEY `uid` (`uid`),
  ADD KEY `cid` (`cid`),
  ADD KEY `notify` (`notify`),
  ADD KEY `email_notify` (`email_notify`);

--
-- Indexes for table `surveys`
--
ALTER TABLE `surveys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `owner` (`owner`),
  ADD KEY `course` (`course`);

--
-- Indexes for table `survey_answers`
--
ALTER TABLE `survey_answers`
  ADD KEY `question` (`question`);

--
-- Indexes for table `survey_answerset`
--
ALTER TABLE `survey_answerset`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `survey_questions`
--
ALTER TABLE `survey_questions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `survey` (`survey`),
  ADD KEY `id_2` (`id`);

--
-- Indexes for table `title_issuers`
--
ALTER TABLE `title_issuers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `id_2` (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `uid` (`uid`),
  ADD KEY `uname` (`uname`),
  ADD KEY `mail` (`mail`);

--
-- Indexes for table `vocabularysets`
--
ALTER TABLE `vocabularysets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `user` (`user`),
  ADD KEY `course` (`course`);

--
-- Indexes for table `vocabulary_scores`
--
ALTER TABLE `vocabulary_scores`
  ADD UNIQUE KEY `word_id` (`word_id`),
  ADD KEY `word_id_2` (`word_id`),
  ADD KEY `user` (`user`);

--
-- Indexes for table `vocabulary_words`
--
ALTER TABLE `vocabulary_words`
  ADD PRIMARY KEY (`word_id`),
  ADD UNIQUE KEY `word_id` (`word_id`),
  ADD KEY `set_id` (`set_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`uid`),
  ADD CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`course`) REFERENCES `courses` (`id`);

--
-- Constraints for table `announcement_comments`
--
ALTER TABLE `announcement_comments`
  ADD CONSTRAINT `announcement_comments_ibfk_1` FOREIGN KEY (`announcement`) REFERENCES `announcements` (`id`),
  ADD CONSTRAINT `announcement_comments_ibfk_2` FOREIGN KEY (`user`) REFERENCES `users` (`uid`),
  ADD CONSTRAINT `announcement_comments_ibfk_3` FOREIGN KEY (`user`) REFERENCES `users` (`uid`);

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`course`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `assets_ibfk_2` FOREIGN KEY (`owner`) REFERENCES `users` (`uid`);

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `users` (`uid`),
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`course`) REFERENCES `courses` (`id`);

--
-- Constraints for table `chapters`
--
ALTER TABLE `chapters`
  ADD CONSTRAINT `chapters_ibfk_1` FOREIGN KEY (`course`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `chat_memberships`
--
ALTER TABLE `chat_memberships`
  ADD CONSTRAINT `chat_memberships_ibfk_3` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`chat_id`);

--
-- Constraints for table `confirmation_codes`
--
ALTER TABLE `confirmation_codes`
  ADD CONSTRAINT `confirmation_codes_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `users` (`uid`);

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`course`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `document_permissions`
--
ALTER TABLE `document_permissions`
  ADD CONSTRAINT `document_permissions_ibfk_1` FOREIGN KEY (`doc_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_permissions_ibfk_2` FOREIGN KEY (`user`) REFERENCES `users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `followings`
--
ALTER TABLE `followings`
  ADD CONSTRAINT `followings_ibfk_1` FOREIGN KEY (`follower`) REFERENCES `users` (`uid`),
  ADD CONSTRAINT `followings_ibfk_2` FOREIGN KEY (`following`) REFERENCES `users` (`uid`);

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`),
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`cid`) REFERENCES `courses` (`id`);

--
-- Constraints for table `surveys`
--
ALTER TABLE `surveys`
  ADD CONSTRAINT `surveys_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `users` (`uid`),
  ADD CONSTRAINT `surveys_ibfk_2` FOREIGN KEY (`course`) REFERENCES `courses` (`id`);

--
-- Constraints for table `survey_answers`
--
ALTER TABLE `survey_answers`
  ADD CONSTRAINT `survey_answers_ibfk_1` FOREIGN KEY (`question`) REFERENCES `survey_questions` (`id`);

--
-- Constraints for table `survey_questions`
--
ALTER TABLE `survey_questions`
  ADD CONSTRAINT `survey_questions_ibfk_1` FOREIGN KEY (`survey`) REFERENCES `surveys` (`id`);

--
-- Constraints for table `vocabularysets`
--
ALTER TABLE `vocabularysets`
  ADD CONSTRAINT `vocabularysets_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `vocabularysets_ibfk_2` FOREIGN KEY (`course`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vocabulary_scores`
--
ALTER TABLE `vocabulary_scores`
  ADD CONSTRAINT `vocabulary_scores_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `vocabulary_scores_ibfk_2` FOREIGN KEY (`word_id`) REFERENCES `vocabulary_words` (`word_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vocabulary_words`
--
ALTER TABLE `vocabulary_words`
  ADD CONSTRAINT `vocabulary_words_ibfk_2` FOREIGN KEY (`set_id`) REFERENCES `vocabularysets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
