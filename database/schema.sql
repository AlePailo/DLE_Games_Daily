-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Apr 28, 2026 alle 23:48
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dle_games_daily`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `attribute_definitions`
--

CREATE TABLE `attribute_definitions` (
  `id` int(10) UNSIGNED NOT NULL,
  `franchise_id` int(10) UNSIGNED NOT NULL,
  `attribute_key` varchar(100) NOT NULL,
  `attribute_label` varchar(100) NOT NULL,
  `display_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `characters`
--

CREATE TABLE `characters` (
  `id` int(10) UNSIGNED NOT NULL,
  `franchise_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `character_attributes`
--

CREATE TABLE `character_attributes` (
  `character_id` int(10) UNSIGNED NOT NULL,
  `attribute_definition_id` int(10) UNSIGNED NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `daily_challenges`
--

CREATE TABLE `daily_challenges` (
  `id` int(10) UNSIGNED NOT NULL,
  `franchise_id` int(10) UNSIGNED NOT NULL,
  `character_id` int(10) UNSIGNED NOT NULL,
  `challenge_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `franchises`
--

CREATE TABLE `franchises` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `icon_url` varchar(255) NOT NULL,
  `bg_image_url` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `friendships`
--

CREATE TABLE `friendships` (
  `id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `receiver_id` int(10) UNSIGNED NOT NULL,
  `status` enum('pending','accepted','blocked') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `game_attempts`
--

CREATE TABLE `game_attempts` (
  `id` int(10) UNSIGNED NOT NULL,
  `session_id` int(10) UNSIGNED NOT NULL,
  `guessed_char_id` int(10) UNSIGNED NOT NULL,
  `attempt_number` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `game_attempt_results`
--

CREATE TABLE `game_attempt_results` (
  `id` int(10) UNSIGNED NOT NULL,
  `attempt_id` int(10) UNSIGNED NOT NULL,
  `attribute_def_id` int(10) UNSIGNED NOT NULL,
  `result_status` enum('correct','wrong','partial') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `game_sessions`
--

CREATE TABLE `game_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `challenge_id` int(10) UNSIGNED NOT NULL,
  `attempts_count` tinyint(4) NOT NULL DEFAULT 0,
  `solved` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `users_oauth`
--

CREATE TABLE `users_oauth` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `provider` enum('google','x','facebook') NOT NULL,
  `provider_id` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `users_passwords`
--

CREATE TABLE `users_passwords` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `verification_token` varchar(255) DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `user_franchise_stats`
--

CREATE TABLE `user_franchise_stats` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `franchise_id` int(10) UNSIGNED NOT NULL,
  `games_played` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `games_won` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `current_streak` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `max_streak` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `avg_attempts` float NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `attribute_definitions`
--
ALTER TABLE `attribute_definitions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attr_def_franchise_id` (`franchise_id`);

--
-- Indici per le tabelle `characters`
--
ALTER TABLE `characters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_characters_franchise_id` (`franchise_id`);

--
-- Indici per le tabelle `character_attributes`
--
ALTER TABLE `character_attributes`
  ADD PRIMARY KEY (`character_id`,`attribute_definition_id`),
  ADD KEY `idx_char_attr_character_id` (`character_id`),
  ADD KEY `idx_char_attr_definition_id` (`attribute_definition_id`);

--
-- Indici per le tabelle `daily_challenges`
--
ALTER TABLE `daily_challenges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_daily_challenge` (`franchise_id`,`challenge_date`),
  ADD KEY `idx_daily_challenges_character_id` (`character_id`);

--
-- Indici per le tabelle `franchises`
--
ALTER TABLE `franchises`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_franchises_slug` (`slug`);

--
-- Indici per le tabelle `friendships`
--
ALTER TABLE `friendships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_friendships_pair` (`sender_id`,`receiver_id`),
  ADD KEY `idx_friendships_sender_id` (`sender_id`),
  ADD KEY `idx_friendships_receiver_id` (`receiver_id`);

--
-- Indici per le tabelle `game_attempts`
--
ALTER TABLE `game_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_game_attempts_session_id` (`session_id`),
  ADD KEY `idx_game_attempts_guessed_char_id` (`guessed_char_id`);

--
-- Indici per le tabelle `game_attempt_results`
--
ALTER TABLE `game_attempt_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attempt_results_attempt_id` (`attempt_id`),
  ADD KEY `idx_attempt_results_attr_def_id` (`attribute_def_id`);

--
-- Indici per le tabelle `game_sessions`
--
ALTER TABLE `game_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_game_sessions_user_challenge` (`user_id`,`challenge_id`),
  ADD KEY `idx_game_sessions_challenge_id` (`challenge_id`);

--
-- Indici per le tabelle `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_remember_tokens_user_id` (`user_id`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- Indici per le tabelle `users_oauth`
--
ALTER TABLE `users_oauth`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_oauth_provider` (`provider`,`provider_id`),
  ADD KEY `idx_users_oauth_user_id` (`user_id`);

--
-- Indici per le tabelle `users_passwords`
--
ALTER TABLE `users_passwords`
  ADD PRIMARY KEY (`user_id`);

--
-- Indici per le tabelle `user_franchise_stats`
--
ALTER TABLE `user_franchise_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_franchise_stats` (`user_id`,`franchise_id`),
  ADD KEY `idx_stats_franchise_games_won` (`franchise_id`,`games_won`),
  ADD KEY `idx_stats_franchise_current_streak` (`franchise_id`,`current_streak`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `attribute_definitions`
--
ALTER TABLE `attribute_definitions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `characters`
--
ALTER TABLE `characters`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `daily_challenges`
--
ALTER TABLE `daily_challenges`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `franchises`
--
ALTER TABLE `franchises`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `friendships`
--
ALTER TABLE `friendships`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `game_attempts`
--
ALTER TABLE `game_attempts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `game_attempt_results`
--
ALTER TABLE `game_attempt_results`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `game_sessions`
--
ALTER TABLE `game_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `users_oauth`
--
ALTER TABLE `users_oauth`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `user_franchise_stats`
--
ALTER TABLE `user_franchise_stats`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `attribute_definitions`
--
ALTER TABLE `attribute_definitions`
  ADD CONSTRAINT `fk_attr_def_franchise` FOREIGN KEY (`franchise_id`) REFERENCES `franchises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `characters`
--
ALTER TABLE `characters`
  ADD CONSTRAINT `fk_characters_franchise` FOREIGN KEY (`franchise_id`) REFERENCES `franchises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `character_attributes`
--
ALTER TABLE `character_attributes`
  ADD CONSTRAINT `fk_char_attr_character` FOREIGN KEY (`character_id`) REFERENCES `characters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_char_attr_definition` FOREIGN KEY (`attribute_definition_id`) REFERENCES `attribute_definitions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `daily_challenges`
--
ALTER TABLE `daily_challenges`
  ADD CONSTRAINT `fk_daily_challenges_character` FOREIGN KEY (`character_id`) REFERENCES `characters` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_daily_challenges_franchise` FOREIGN KEY (`franchise_id`) REFERENCES `franchises` (`id`) ON UPDATE CASCADE;

--
-- Limiti per la tabella `friendships`
--
ALTER TABLE `friendships`
  ADD CONSTRAINT `fk_friendships_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_friendships_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `game_attempts`
--
ALTER TABLE `game_attempts`
  ADD CONSTRAINT `fk_game_attempts_character` FOREIGN KEY (`guessed_char_id`) REFERENCES `characters` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_game_attempts_session` FOREIGN KEY (`session_id`) REFERENCES `game_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `game_attempt_results`
--
ALTER TABLE `game_attempt_results`
  ADD CONSTRAINT `fk_attempt_results_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `game_attempts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attempt_results_attr_def` FOREIGN KEY (`attribute_def_id`) REFERENCES `attribute_definitions` (`id`) ON UPDATE CASCADE;

--
-- Limiti per la tabella `game_sessions`
--
ALTER TABLE `game_sessions`
  ADD CONSTRAINT `fk_game_sessions_challenge` FOREIGN KEY (`challenge_id`) REFERENCES `daily_challenges` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_game_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `fk_remember_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `users_oauth`
--
ALTER TABLE `users_oauth`
  ADD CONSTRAINT `fk_users_oauth_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `users_passwords`
--
ALTER TABLE `users_passwords`
  ADD CONSTRAINT `fk_users_passwords_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `user_franchise_stats`
--
ALTER TABLE `user_franchise_stats`
  ADD CONSTRAINT `fk_stats_franchise` FOREIGN KEY (`franchise_id`) REFERENCES `franchises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stats_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
