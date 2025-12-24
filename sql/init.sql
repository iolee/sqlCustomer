SHOW DATABASES;
DROP DATABASE IF EXISTS assign2db ;
CREATE DATABASE assign2db;
USE assign2db;

-- create the tables
SHOW TABLES;
CREATE TABLE product (prodID CHAR(2) NOT NULL, description VARCHAR(30), cost DEC(6,2), quantityonhand INT, PRIMARY KEY (prodID));
CREATE TABLE agent (agentID CHAR(2) NOT NULL,firstname VARCHAR(10),lastname VARCHAR(10),city VARCHAR(15),commission INT,PRIMARY KEY (agentID));
CREATE TABLE customer (cusID CHAR(2) NOT NULL, firstname VARCHAR(10), lastname VARCHAR(10), city VARCHAR(15), phonenumber CHAR(10), agentID CHAR(2), PRIMARY KEY (cusID), FOREIGN KEY (agentID) REFERENCES agent(agentID) ON DELETE RESTRICT);
CREATE TABLE purchases (cusID CHAR(2) NOT NULL, prodID CHAR(2) NOT NULL, quantity int, PRIMARY KEY (cusID, prodID), FOREIGN KEY (prodID) REFERENCES product(prodID), FOREIGN KEY (cusID) REFERENCES customer(cusID));
SHOW TABLES;


-- insert some data
-- insert products
INSERT INTO product(prodID,description,cost,quantityonhand) VALUES ('66','Elbow pads', 14.25, 120),('12','Socks',2.00,100),('99','Helmet',29.00,30), ('88','Roller Blades',75.00,89), ('78','Knee Pads',12.15,70), ('11','Bike',150.00,200),('51','Hockey Stick', 22.00, 100), ('44','Kids pads for wrists',9.99,100);

-- insert agent data

INSERT INTO agent VALUES ('99','Hugh','Grant','Springfield',16), ('22','Courtney','Cox','Springfield',25), ('66','Rosie','ODonnell','New York',100), ('33','David','Letterman','Bedrock',100), ('11','Regis','Philbin','Boston',33), ('12','Rosie','Cox','Ottawa',50), ('19','Laura','Reid', 'Londons',20); 


-- insert customer data

INSERT INTO customer VALUES ('21','Homer','Simpson','Springfield','9056868956','99'), ('31','Sideshow','Bob','Springfield','5196865555','66'), ('12','Monty','Burns','Springfield','5197890000','99'), ('15','Fred','Flintstone','Bedrock','5187772345','11'), ('13','Super','Man','Kypto','4168881234','33'), ('10','Barney','Rubble','Bedrock','5197771234','11'),('14','Peter','Griffens','Providence','9059874567','99'); 

-- insert purchase data

INSERT INTO purchases VALUES ('21', '99', 20);
INSERT INTO purchases VALUES ('21', '12', 14);
INSERT INTO purchases VALUES ('21', '66', 10);
INSERT INTO purchases VALUES ('31', '99', 1);
INSERT INTO purchases VALUES ('31', '12', 2);
INSERT INTO purchases VALUES ('31', '78', 4);
INSERT INTO purchases VALUES ('31', '66', 2);
INSERT INTO purchases VALUES ('15', '66', 2);
INSERT INTO purchases VALUES ('15', '78', 2);
INSERT INTO purchases VALUES ('14', '66', 19);

-- check the tables

SELECT * FROM product;
SELECT * FROM agent;
SELECT * FROM customer;
SELECT * FROM purchases;





