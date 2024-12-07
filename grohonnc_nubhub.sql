-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 02, 2024 at 11:39 PM
-- Server version: 8.0.37
-- PHP Version: 8.3.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `grohonnc_nubhub`
--

-- --------------------------------------------------------

--
-- Table structure for table `notice`
--

CREATE TABLE `notice` (
  `id` int NOT NULL,
  `short_name` varchar(50) NOT NULL,
  `notice` text NOT NULL,
  `session` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notice`
--

INSERT INTO `notice` (`id`, `short_name`, `notice`, `session`, `created_at`) VALUES
(1, 'MTR', 'hi', 'fall24', '2024-11-30 23:37:40'),
(2, 'MTR', 'vvvv', 'fall24', '2024-11-30 23:53:18');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `session` varchar(50) NOT NULL,
  `semester` varchar(50) NOT NULL,
  `short_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `active` int NOT NULL DEFAULT '0',
  `project` varchar(255) NOT NULL,
  `storage` text NOT NULL,
  `ftp_pass` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `student_id`, `session`, `semester`, `short_name`, `active`, `project`, `storage`, `ftp_pass`) VALUES
(41, 12, '41220100052', 'fall24', '9', NULL, 0, '', '', '8253b5ca-af49'),
(44, 14, '41220100050', 'fall24', '8', NULL, 0, '', '', '8253b7a6-af49'),
(46, 17, '41220100053', 'fall24', '7', 'MTR', 0, '', '60000000', '*1hG15DYmwgA'),
(54, 21, '1630', 'fall24', '9', 'MTR', 0, '', '', ''),
(56, 22, '123', 'fall24', '9', 'MTR', 0, '', '', ''),
(57, 23, '4432', 'fall24', '9', 'MTR', 0, '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `student_team`
--

CREATE TABLE `student_team` (
  `id` int NOT NULL,
  `leader_id` varchar(250) NOT NULL,
  `student_id` varchar(250) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `session` varchar(100) NOT NULL,
  `semester` int NOT NULL,
  `short_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `student_team`
--

INSERT INTO `student_team` (`id`, `leader_id`, `student_id`, `student_name`, `session`, `semester`, `short_name`) VALUES
(110, '41220100053', '41220100053', 'sondha', 'fall24', 7, 'MTR'),
(114, '123', '123', 'test1', 'fall24', 9, 'MTR'),
(115, '41220100053', '4432', 'test2', 'fall24', 7, 'MTR'),
(117, '41220100053', '1630', 'anik', 'fall24', 9, NULL);

--
-- Triggers `student_team`
--
DELIMITER $$
CREATE TRIGGER `1delete_blank_rows_student_team` AFTER INSERT ON `student_team` FOR EACH ROW BEGIN
    -- Check if student_name or student_id is blank or NULL
    IF NEW.student_name = '' OR NEW.student_name IS NULL OR NEW.student_id = '' OR NEW.student_id IS NULL THEN
        DELETE FROM student_team WHERE student_id = NEW.student_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `delete_blank_rows_student_team` BEFORE INSERT ON `student_team` FOR EACH ROW BEGIN
    -- Check if student_name or student_id is blank or NULL
    IF NEW.student_name = '' OR NEW.student_name IS NULL OR NEW.student_id = '' OR NEW.student_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'student_name and student_id cannot be blank';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `short_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `short_name`) VALUES
(1, 6, 'MTR'),
(2, 10, 'KUDD'),
(3, 13, 'KAF');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_slot`
--

CREATE TABLE `teacher_slot` (
  `id` int NOT NULL,
  `short_name` varchar(255) NOT NULL,
  `session` varchar(255) NOT NULL,
  `open` int DEFAULT '1',
  `total_team` int NOT NULL,
  `project_open` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `teacher_slot`
--

INSERT INTO `teacher_slot` (`id`, `short_name`, `session`, `open`, `total_team`, `project_open`) VALUES
(6, 'KAF', 'fall24', 1, 3, 0),
(9, 'MTR', 'summer25', 1, 6, 1),
(17, 'MTR', 'fall24', 1, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tutorials`
--

CREATE TABLE `tutorials` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tutorials`
--

INSERT INTO `tutorials` (`id`, `title`, `link`, `created_at`) VALUES
(1, 'tttt', 'https://www.instagram.com/p/DCrqNwbyGJs/?hl=en', '2024-12-01 00:08:22');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `active` int NOT NULL DEFAULT '1',
  `role` enum('student','teacher') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `active`, `role`, `created_at`) VALUES
(6, 'Tahsin', 'tahsin@gmail.com', '0f338476124d51dd49e1bf8e922fce7c', 1, 'teacher', '2024-11-21 15:04:36'),
(10, 'Kuddus', 'kuddus@gmail.com', 'e18b8f511efbe5b8b337a0524212d235', 1, 'teacher', '2024-11-21 15:04:36'),
(12, 'Md Ahshan Habib', 'ahshanhabibtanvir@gmail.com', 'e18b8f511efbe5b8b337a0524212d235', 1, 'student', '2024-11-25 18:24:02'),
(13, 'kafi', 'kafi@gmail.com', '413bda5735be31d191b14e82332c332d', 1, 'teacher', '2024-11-21 15:04:36'),
(14, 'kukrrrrr', 'kukurrr@gmail.com', '7c788e7daff4cd33e31cbc70fbf9d0a3', 1, 'student', '2024-11-25 18:24:02'),
(17, 'sondha', 'sondha@gmail.com', '870d90b43c5fc7d6ce8a055d682fb4fe', 1, 'student', '2024-11-26 10:26:09'),
(21, 'anik', 'anika@gmail.com', 'f6e62d6d0b20659d8b62fc815824a03b', 1, 'student', '2024-11-30 20:09:58'),
(22, 'test1', 'test1@gmail.com', '245cf079454dc9a3374a7c076de247cc', 1, 'student', '2024-11-30 22:12:16'),
(23, 'test2', 'test2@gmail.com', '3c4f419e8cd958690d0d14b3b89380d3', 1, 'student', '2024-11-30 22:13:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notice`
--
ALTER TABLE `notice`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_short_name` (`short_name`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_students_short_name` (`short_name`);

--
-- Indexes for table `student_team`
--
ALTER TABLE `student_team`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `short_name` (`short_name`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `teacher_slot`
--
ALTER TABLE `teacher_slot`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_short_session` (`short_name`,`session`);

--
-- Indexes for table `tutorials`
--
ALTER TABLE `tutorials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notice`
--
ALTER TABLE `notice`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `student_team`
--
ALTER TABLE `student_team`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `teacher_slot`
--
ALTER TABLE `teacher_slot`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `tutorials`
--
ALTER TABLE `tutorials`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notice`
--
ALTER TABLE `notice`
  ADD CONSTRAINT `fk_short_name` FOREIGN KEY (`short_name`) REFERENCES `teachers` (`short_name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_short_name` FOREIGN KEY (`short_name`) REFERENCES `teachers` (`short_name`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_slot`
--
ALTER TABLE `teacher_slot`
  ADD CONSTRAINT `fk_teacher_slot_short_name` FOREIGN KEY (`short_name`) REFERENCES `teachers` (`short_name`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
