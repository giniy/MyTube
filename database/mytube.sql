-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2025 at 09:02 AM
-- Server version: 8.0.36
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mytube`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `video_id` int NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `parent_id` int DEFAULT NULL,
  `like_count` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `user_id`, `video_id`, `comment`, `created_at`, `parent_id`, `like_count`) VALUES
(2, 1, 3, 'Nice design', '2025-04-26 22:30:05', NULL, 1),
(3, 1, 3, 'I love it', '2025-04-26 22:30:13', NULL, 0),
(4, 1, 4, 'Nice song', '2025-04-27 07:07:38', NULL, 1),
(5, 1, 7, 'nice song', '2025-04-28 10:04:43', NULL, 2),
(6, 1, 7, 'yea!', '2025-04-28 10:44:23', 5, 2),
(7, 1, 3, 'yes', '2025-04-28 10:53:30', 2, 0),
(8, 1, 7, 'Amazing song', '2025-04-28 10:55:43', NULL, 2),
(9, 3, 6, 'Good palace', '2025-04-28 11:16:50', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `comment_likes`
--

CREATE TABLE `comment_likes` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `comment_likes`
--

INSERT INTO `comment_likes` (`id`, `user_id`, `comment_id`, `created_at`) VALUES
(1, 1, 5, '2025-04-28 05:01:24'),
(2, 1, 6, '2025-04-28 05:14:44'),
(3, 1, 4, '2025-04-28 05:22:54'),
(4, 1, 2, '2025-04-28 05:23:23'),
(5, 1, 8, '2025-04-28 05:25:53'),
(6, 3, 8, '2025-04-28 05:46:27'),
(7, 3, 5, '2025-04-28 05:46:28'),
(8, 3, 6, '2025-04-28 05:46:30'),
(9, 1, 9, '2025-04-28 05:47:30');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `created_at`, `is_read`) VALUES
(1, 'rose', 'gpendare@gmail.com', 'Hi there, Can we have a team meeting for a general discussion on a business idea?', '2025-04-26 19:21:34', 0),
(2, 'Gulab', 'sdfsfsf@gmail.com', 'Hi Sir, can we have a team meeting on a business idea?', '2025-04-26 19:26:26', 0),
(3, 'Gulab', 'sdfsfsf@gmail.com', 'Hi Sir, can we have a team meeting on a business idea?', '2025-04-26 19:27:22', 0),
(4, 'Gulab', 'sdfsfsf@gmail.com', 'Hi Sir, can we have a team meeting on a business idea?', '2025-04-26 19:28:03', 0),
(5, 'Gulab', 'sdfsfsf@gmail.com', 'Hi Sir, can we have a team meeting?', '2025-04-26 19:28:21', 0),
(6, 'Gulab', 'sdfsfsf@gmail.com', 'Hi Sir, can we schedule a team meeting?', '2025-04-26 19:33:31', 0);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `feedback_type` enum('suggestion','bug','compliment','general') NOT NULL,
  `message` text NOT NULL,
  `rating` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_processed` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `email`, `feedback_type`, `message`, `rating`, `created_at`, `is_processed`) VALUES
(1, 'Gulab', 'root@gmail.com', 'general', 'Nice work', 0, '2025-04-26 19:30:41', 0);

-- --------------------------------------------------------

--
-- Table structure for table `forum_categories`
--

CREATE TABLE `forum_categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `forum_categories`
--

INSERT INTO `forum_categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'General Discussion', 'Talk about anything related to MyTube', '2025-04-26 18:45:44'),
(2, 'Technical Help', 'Get help with technical issues', '2025-04-26 18:45:44'),
(3, 'Feature Requests', 'Suggest new features for MyTube', '2025-04-26 18:45:44'),
(4, 'Video Production', 'Tips and tricks for creating videos', '2025-04-26 18:45:44'),
(5, 'Site Feedback', 'Share your feedback about the site', '2025-04-26 18:45:44');

-- --------------------------------------------------------

--
-- Table structure for table `forum_replies`
--

CREATE TABLE `forum_replies` (
  `id` int NOT NULL,
  `topic_id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `forum_replies`
--

INSERT INTO `forum_replies` (`id`, `topic_id`, `user_id`, `content`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Did you try to upload low size of video?', '2025-04-26 18:47:23', '2025-04-26 18:47:23'),
(2, 2, 1, 'Yes, we are working on it.', '2025-04-27 17:58:09', '2025-04-27 17:58:09');

-- --------------------------------------------------------

--
-- Table structure for table `forum_topics`
--

CREATE TABLE `forum_topics` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `is_faq` tinyint(1) DEFAULT '0',
  `is_closed` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `forum_topics`
--

INSERT INTO `forum_topics` (`id`, `category_id`, `user_id`, `title`, `content`, `is_faq`, `is_closed`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Upload issue', 'I am not able to upload the video on the site.', 0, 0, '2025-04-26 18:46:26', '2025-04-26 18:47:23'),
(2, 1, 1, 'How to share the video?', 'I am not able to shared the video , there is only copy link option is available.', 0, 0, '2025-04-27 17:57:33', '2025-04-27 17:58:09');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `video_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `video_id`, `created_at`) VALUES
(11, 1, 3, '2025-04-26 22:05:02'),
(15, 1, 4, '2025-04-27 16:55:59'),
(16, 1, 5, '2025-04-27 19:18:25'),
(17, 1, 7, '2025-04-27 19:21:23'),
(18, 3, 7, '2025-04-28 05:46:24'),
(19, 3, 6, '2025-04-28 05:46:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'root', 'root@gmail.com', '$2y$10$P2PPGMvZxJMvSuKYER89eun6nf/8dTSbrWol.sii0vERfBnhILKMi', '2025-04-26 20:48:55'),
(2, 'ravi', 'ravi@gmail.com', '$2y$10$u0QMduHBs21fCcHxqoc1UOQ8GfjeWqEZ5.I/2wRnBqymJLe4s.QHe', '2025-04-28 11:02:22'),
(3, 'rosep', 'rose.p@gmail.com', '$2y$10$RVskGnC7S/PStjUG5JTCeOeuRzDYl0uWJizmPEd8FN7iNqFqjEdEG', '2025-04-28 11:15:42');

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `video_file` varchar(255) NOT NULL,
  `thumbnail_file` varchar(255) NOT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `view_count` int DEFAULT '0',
  `share_count` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `videos`
--

INSERT INTO `videos` (`id`, `user_id`, `title`, `description`, `video_file`, `thumbnail_file`, `uploaded_at`, `view_count`, `share_count`) VALUES
(3, 1, 'Mehendi Tatoo', 'Amazing Design of Mehendi Tatoo', 'video_680d10e01c7f76.84740889.mp4', 'thumb_680d10e01c8019.99983827.png', '2025-04-26 22:29:12', 0, 1),
(4, 1, 'Sigrid - Mirror', 'Sigrid - Mirror is a song by Norwegian singer-songwriter Sigrid from her second studio album,', 'video_680d89677336d2.88466596.mp4', 'thumb_680d8967733790.33137399.png', '2025-04-27 07:03:27', 0, 2),
(5, 1, 'Nature | Farm', 'Nature views and farm', 'video_680e82f833b179.99706520.mp4', 'thumb_680e82f833b1e7.30486898.png', '2025-04-28 00:48:16', 0, 1),
(6, 1, 'Palace Tour', 'Let me show you my palace', 'video_680e833a0a8786.15406396.mp4', 'thumb_680e833a0a87e1.29644312.png', '2025-04-28 00:49:22', 0, 0),
(7, 1, 'In the Mirror | Sigrid Song', 'Artist: Sigrid\r\nReleased: 2022 | Album: How to Let Go\r\nGenres: Disco, Nigerian R&B, Afropop, Pop', 'video_680e83aba19329.60549494.mp4', 'thumb_680e83aba19362.81882149.png', '2025-04-28 00:51:15', 0, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `video_id` (`video_id`);

--
-- Indexes for table `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`comment_id`),
  ADD KEY `comment_id` (`comment_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forum_categories`
--
ALTER TABLE `forum_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forum_replies`
--
ALTER TABLE `forum_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`video_id`),
  ADD KEY `video_id` (`video_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `comment_likes`
--
ALTER TABLE `comment_likes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `forum_categories`
--
ALTER TABLE `forum_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `forum_replies`
--
ALTER TABLE `forum_replies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `forum_topics`
--
ALTER TABLE `forum_topics`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`);

--
-- Constraints for table `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD CONSTRAINT `comment_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `comment_likes_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`);

--
-- Constraints for table `forum_replies`
--
ALTER TABLE `forum_replies`
  ADD CONSTRAINT `forum_replies_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `forum_topics` (`id`),
  ADD CONSTRAINT `forum_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD CONSTRAINT `forum_topics_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `forum_categories` (`id`),
  ADD CONSTRAINT `forum_topics_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `videos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
