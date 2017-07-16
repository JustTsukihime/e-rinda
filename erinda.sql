-- phpMyAdmin SQL Dump
-- version 4.5.0.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jul 11, 2017 at 08:05 AM
-- Server version: 10.1.13-MariaDB
-- PHP Version: 5.6.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `erinda`
--

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `Key` varchar(30) NOT NULL,
  `Value` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`Key`, `Value`) VALUES
('PurgeTimeout', '3600');

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE `options` (
  `name` varchar(50) NOT NULL,
  `value` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`name`, `value`) VALUES
('Break', 0);

-- --------------------------------------------------------

--
-- Table structure for table `queuers`
--

CREATE TABLE `queuers` (
  `ID` bigint(20) UNSIGNED NOT NULL,
  `QueueID` bigint(20) UNSIGNED NOT NULL,
  `Name` varchar(70) NOT NULL,
  `Enqueued` int(11) NOT NULL,
  `Called` int(11) DEFAULT NULL,
  `Dequeued` int(11) DEFAULT NULL,
  `Out` int(11) DEFAULT NULL,
  `Status` enum('Waiting','Called','Handling','Out','Purged') DEFAULT 'Waiting'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `queues`
--

CREATE TABLE `queues` (
  `ID` bigint(20) UNSIGNED NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Status` enum('Open','Closed') NOT NULL,
  `StartTime` int(11) NOT NULL,
  `EndTime` int(11) NOT NULL,
  `PurgeTimeout` int(11) NOT NULL DEFAULT '3600'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `queues`
--

INSERT INTO `queues` (`ID`, `Name`, `Status`, `StartTime`, `EndTime`, `PurgeTimeout`) VALUES
(1, 'DF Dokumentu iesniegšanas rinda', 'Open', 0, 0, 900);

-- --------------------------------------------------------

--
-- Table structure for table `registars`
--

CREATE TABLE `registars` (
  `UserID` bigint(20) UNSIGNED NOT NULL,
  `QueueID` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `registars`
--

INSERT INTO `registars` (`UserID`, `QueueID`) VALUES
(13, 1),
(14, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` bigint(20) UNSIGNED NOT NULL,
  `Login` char(20) NOT NULL,
  `Password` char(255) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `Login`, `Password`, `status`) VALUES
(13, 'datoriki', '$2y$11$OqnGGtjBxeqz7MAmDBrKW.0kf4CcZ1DNxWhgBI2kJ3EgPqNeSM2VG', 0),
(14, 'dokumenti', '$2y$11$mBeUbjoujtxYPxCdzAkdUe77pwLgksjoi7a8QUN5kfGwHa7OMc8pe', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`Key`);

--
-- Indexes for table `queuers`
--
ALTER TABLE `queuers`
  ADD UNIQUE KEY `ID` (`ID`),
  ADD KEY `QueueID` (`QueueID`);

--
-- Indexes for table `queues`
--
ALTER TABLE `queues`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `registars`
--
ALTER TABLE `registars`
  ADD PRIMARY KEY (`UserID`,`QueueID`),
  ADD KEY `QueueID` (`QueueID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`) USING BTREE,
  ADD UNIQUE KEY `Login` (`Login`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `queuers`
--
ALTER TABLE `queuers`
  MODIFY `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `queues`
--
ALTER TABLE `queues`
  MODIFY `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `queuers`
--
ALTER TABLE `queuers`
  ADD CONSTRAINT `queuers_ibfk_1` FOREIGN KEY (`QueueID`) REFERENCES `queues` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `registars`
--
ALTER TABLE `registars`
  ADD CONSTRAINT `registars_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `registars_ibfk_2` FOREIGN KEY (`QueueID`) REFERENCES `queues` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
