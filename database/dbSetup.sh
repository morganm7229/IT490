$dbPassword = "doogis123";
$DBUSER = "database";
# Create database steamTag and grant all privileges to user defined by $DBUSER
mysql -e "CREATE DATABASE steamTag; GRANT ALL PRIVILEGES ON steamTag.* TO '$DBUSER'@'localhost' IDENTIFIED BY '$dbPassword'; FLUSH PRIVILEGES;"
# Create table accounts
mysql -e "CREATE TABLE `accounts` (
  `accid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(25) DEFAULT NULL,
  `lifetimePoints` int DEFAULT NULL,
  `gamesWon` int DEFAULT NULL,
  `publicProfile` tinyint(1) DEFAULT NULL,
  `publicFriends` tinyint(1) DEFAULT NULL,
  `publicAchievements` tinyint(1) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `highestScore` int DEFAULT NULL,
  `gamesPlayed` int DEFAULT NULL,
  `sessionID` varchar(128) DEFAULT NULL,
  `lastLogin` datetime DEFAULT NULL,
  PRIMARY KEY (`accid`),
  UNIQUE KEY `name` (`name`)
);"
# Create table friends
mysql -e "CREATE TABLE `friends` (
  `accID` int NOT NULL,
  `friendID` int NOT NULL,
  `friendUsername` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`accID`,`friendID`)
);"
# Create table playerAchievements
mysql -e "CREATE TABLE `playerAchievements` (
  `accID` int NOT NULL,
  `achievement` varchar(50) NOT NULL,
  PRIMARY KEY (`accID`,`achievement`)
);"
# Create table steamGames
mysql -e "CREATE TABLE `steamGames` (
  `steamID` int NOT NULL,
  `type` varchar(20) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `shortDescription` varchar(500) DEFAULT NULL,
  `headerImage` varchar(200) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `genres` varchar(1000) DEFAULT NULL,
  `categories` varchar(1000) DEFAULT NULL,
  `releaseDate` varchar(25) DEFAULT NULL,
  `background` varchar(200) DEFAULT NULL,
  `mature` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`steamID`)
);"
# Create table lobbies
mysql -e "CREATE TABLE `lobbies` (
  `lobbyID` int NOT NULL,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`lobbyID`)
);"

echo "Database setup complete."