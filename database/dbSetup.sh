# Install PHP, the php-amqp extension, and the mysql-server package
echo "Installing PHP, php-amqp, mysql-server, and php-mysqli..."
apt-get update
apt-get install -y php php-amqp mysql-server php-mysqli
echo "Done."

echo "Setting up database..."
# create random password
PASSWDDB="$(openssl rand -base64 12)"

MAINDB="quizDB"

echo "Please enter root user MySQL password!"
mysql -uroot -p${rootpasswd} << EOF
  SET GLOBAL validate_password.policy=LOW;
  CREATE DATABASE ${MAINDB} /*\!40100 DEFAULT CHARACTER SET utf8 */;
  CREATE USER ${MAINDB}@localhost IDENTIFIED WITH mysql_native_password BY '${PASSWDDB}';
  GRANT ALL PRIVILEGES ON ${MAINDB}.* TO '${MAINDB}'@'localhost';

  # Create tables
  USE ${MAINDB};
  CREATE TABLE accounts (
    accid INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(25) DEFAULT NULL,
    lifetimePoints INT DEFAULT NULL,
    gamesWon INT DEFAULT NULL,
    publicProfile TINYINT(1) DEFAULT NULL,
    publicFriends TINYINT(1) DEFAULT NULL,
    publicAchievements TINYINT(1) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    password VARCHAR(255) DEFAULT NULL,
    highestScore INT DEFAULT NULL,
    gamesPlayed INT DEFAULT NULL,
    sessionID VARCHAR(128) DEFAULT NULL,
    lastLogin DATETIME DEFAULT NULL,
    UNIQUE KEY name (name)
  );
  CREATE TABLE friends (
    accID INT NOT NULL,
    friendID INT NOT NULL,
    friendUsername VARCHAR(25) DEFAULT NULL,
    PRIMARY KEY (accID,friendID)
  );
  CREATE TABLE playerAchievements (
    accID INT NOT NULL,
    achievement VARCHAR(50) NOT NULL,
    PRIMARY KEY (accID,achievement)
  );
  CREATE TABLE steamGames (
    steamID INT NOT NULL,
    type VARCHAR(20) DEFAULT NULL,
    name VARCHAR(50) DEFAULT NULL,
    shortDescription VARCHAR(500) DEFAULT NULL,
    headerImage VARCHAR(200) DEFAULT NULL,
    website VARCHAR(100) DEFAULT NULL,
    genres VARCHAR(1000) DEFAULT NULL,
    categories VARCHAR(1000) DEFAULT NULL,
    releaseDate VARCHAR(25) DEFAULT NULL,
    background VARCHAR(200) DEFAULT NULL,
    mature TINYINT(1) DEFAULT NULL,
    PRIMARY KEY (steamID)
  );
  CREATE TABLE lobbies (
    lobbyID INT NOT NULL,
    status INT DEFAULT NULL,
    PRIMARY KEY (lobbyID)
  );

  FLUSH PRIVILEGES;
EOF
echo "Database ${MAINDB} and user ${MAINDB} created."

echo "dbIP=localhost" > database.ini
echo "dbUser=$MAINDB" >> database.ini
echo "dbPassword=$PASSWDDB" >> database.ini
echo "dbName=$MAINDB" >> database.ini

touch host.ini

echo "Database setup complete."

echo "Installing RabbitMQ..."
apt-get install -y rabbitmq-server > /dev/null

# Start RabbitMQ
systemctl start rabbitmq-server

# Enable RabbitMQ to automatically start on boot
systemctl enable rabbitmq-server

echo "Enabling RabbitMQ Management Plugin..."
rabbitmq-plugins enable rabbitmq_management

echo "Installing RabbitMQ Admin CLI..."
curl  http://localhost:15672/cli/rabbitmqadmin --output /usr/local/bin/rabbitmqadmin
chmod +x /usr/local/bin/rabbitmqadmin

# Prompt the user for details
echo "Enter a RabbitMQ username:"
read rabbitmq_username
echo "Enter a RabbitMQ password:"
read -s rabbitmq_password
echo "Enter a vhost name:"
read vhost_name
echo "Enter an exchange name:"
read exchange_name
echo "Enter a queue name:"
read queue_name

rabbitmqctl add_vhost $vhost_name

echo "Adding user"
rabbitmqctl add_user $rabbitmq_username $rabbitmq_password
echo "Setting user tags"
rabbitmqctl set_user_tags $rabbitmq_username management
echo "Setting user permissions"
rabbitmqctl set_permissions -p $vhost_name $rabbitmq_username ".*" ".*" ".*"

# Create the exchange, queue, and binding
echo "Creating exchange"
rabbitmqadmin declare exchange --username=$rabbitmq_username --password=$rabbitmq_password --vhost=$vhost_name name=$exchange_name type=direct
echo "Creating queue"
rabbitmqadmin declare queue --username=$rabbitmq_username --password=$rabbitmq_password --vhost=$vhost_name name=$queue_name durable=true
echo "Creating binding between exchange and queue"
rabbitmqadmin declare binding --username=$rabbitmq_username --password=$rabbitmq_password --vhost=$vhost_name source=$exchange_name destination=$queue_name destination_type=queue routing_key=*
echo "RabbitMQ setup complete"

echo "Creating testRabbitMQ.ini" 
# Write the values to a file
cat > testRabbitMQ.ini <<EOF
[testServer]
BROKER_HOST = localhost
BROKER_PORT = 5672
USER = $rabbitmq_username
PASSWORD = $rabbitmq_password
VHOST = $vhost_name
EXCHANGE = $exchange_name
QUEUE = $queue_name
EXCHANGE_TYPE = direct
AUTO_DELETE = true
EOF

echo "Creating systemd service"
cat > /etc/systemd/system/database.service <<EOF
[Unit]
Description=Database

[Service]
ExecStart=/usr/bin/php $PWD/testRabbitMQServer.php
Restart=always

[Install]
WantedBy=multi-user.target
EOF

echo "Enabling and starting service"
systemctl enable database
systemctl start database
echo "Installation complete"
echo "Service Name: database"