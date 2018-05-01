-- phpMyAdmin SQL Dump
-- version 4.7.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2018-04-20 09:50:56
-- 服务器版本： 5.6.38-log
-- PHP Version: 5.6.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `max_smartlockv2`
--

-- --------------------------------------------------------

--
-- 表的结构 `SLv2_captcha`
--

CREATE TABLE `SLv2_captcha` (
  `sid` char(50) NOT NULL,
  `randmask` char(8) NOT NULL,
  `captchastring` char(6) NOT NULL,
  `validuntil` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `SLv2_room`
--

CREATE TABLE `SLv2_room` (
  `roomNum` smallint(5) UNSIGNED NOT NULL,
  `reserveState` tinyint(1) UNSIGNED NOT NULL,
  `floorNum` tinyint(3) NOT NULL,
  `ipAddress` varchar(15) NOT NULL,
  `lockState` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `SLv2_room`
--

INSERT INTO `SLv2_room` (`roomNum`, `reserveState`, `floorNum`, `ipAddress`, `lockState`) VALUES
(101, 1, 1, '127.0.0.1', 0),
(118, 0, 1, '127.0.0.1', 0),
(201, 0, 2, '127.0.0.1', 0),
(202, 0, 2, '127.0.0.1', 1),
(203, 0, 2, '127.0.0.1', 1),
(204, 0, 2, '127.0.0.1', 1),
(205, 0, 2, '127.0.0.1', 1),
(206, 0, 2, '127.0.0.1', 1),
(207, 0, 2, '127.0.0.1', 1),
(208, 0, 2, '127.0.0.1', 0),
(209, 0, 2, '127.0.0.1', 1),
(210, 0, 2, '127.0.0.1', 1),
(211, 0, 2, '127.0.0.1', 1),
(212, 0, 2, '127.0.0.1', 1),
(213, 0, 2, '127.0.0.1', 1),
(214, 0, 2, '127.0.0.1', 1),
(215, 0, 2, '127.0.0.1', 1),
(301, 0, 3, '127.0.0.1', 0),
(2001, 0, 20, '127.0.0.1', 0),
(3001, 0, 30, '127.0.0.1', 0),
(10001, 0, 100, '127.0.0.1', 0);

-- --------------------------------------------------------

--
-- 表的结构 `SLv2_roomperms`
--

CREATE TABLE `SLv2_roomperms` (
  `addtime` datetime NOT NULL,
  `expiretime` datetime DEFAULT NULL,
  `userid` mediumint(8) UNSIGNED NOT NULL,
  `roomnum` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `SLv2_roomperms`
--

INSERT INTO `SLv2_roomperms` (`addtime`, `expiretime`, `userid`, `roomnum`) VALUES
('2018-04-19 16:44:03', NULL, 3, 201);

-- --------------------------------------------------------

--
-- 表的结构 `SLv2_session`
--

CREATE TABLE `SLv2_session` (
  `sid` char(50) NOT NULL,
  `id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `host` varchar(15) NOT NULL,
  `useragent` varchar(300) NOT NULL,
  `lastactive` int(10) UNSIGNED NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `SLv2_session`
--

INSERT INTO `SLv2_session` (`sid`, `id`, `host`, `useragent`, `lastactive`) VALUES
('mIkKvppwhq0f1WLMhiDt3yA356Ou45Ch152406267136481000', 3, '221.220.161.173', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0', 1524188725),
('Itlt(rvcQ5-8IWHGBkIlwodjg96MPn~x152406935989676800', 0, '114.244.63.29', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0', 1524069359),
('tgTCC8xG7BPYj_ESAPJjgYodvZcYrN5K152415818276483800', 0, '221.220.161.173', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0', 1524158182);

-- --------------------------------------------------------

--
-- 表的结构 `SLv2_user`
--

CREATE TABLE `SLv2_user` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `fullname` varchar(60) CHARACTER SET utf8 NOT NULL,
  `mobilenum` varchar(20) CHARACTER SET utf8 NOT NULL,
  `password` char(32) CHARACTER SET utf8 NOT NULL,
  `salt` char(6) CHARACTER SET utf8 NOT NULL,
  `regtime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `SLv2_user`
--

INSERT INTO `SLv2_user` (`id`, `fullname`, `mobilenum`, `password`, `salt`, `regtime`) VALUES
(3, 'Chris Jiang', '13910132981', '858acd6886b99028f740d01b0fcc3f20', 'uH!Ml1', '2018-04-19 16:44:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `SLv2_captcha`
--
ALTER TABLE `SLv2_captcha`
  ADD UNIQUE KEY `sid` (`sid`);

--
-- Indexes for table `SLv2_room`
--
ALTER TABLE `SLv2_room`
  ADD PRIMARY KEY (`roomNum`),
  ADD KEY `floorNum` (`floorNum`);

--
-- Indexes for table `SLv2_roomperms`
--
ALTER TABLE `SLv2_roomperms`
  ADD KEY `userid` (`userid`,`roomnum`);

--
-- Indexes for table `SLv2_session`
--
ALTER TABLE `SLv2_session`
  ADD UNIQUE KEY `sid` (`sid`);

--
-- Indexes for table `SLv2_user`
--
ALTER TABLE `SLv2_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mobilenum` (`mobilenum`),
  ADD KEY `regtime` (`regtime`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `SLv2_user`
--
ALTER TABLE `SLv2_user`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
