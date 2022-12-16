CREATE TABLE users
(
    user_id INT(3) NOT NULL AUTO_INCREMENT,
    username VARCHAR(16) UNIQUE,
    password VARCHAR(64),
    admin BOOLEAN,
    CONSTRAINT pk_user_id PRIMARY KEY (user_id)
);

CREATE TABLE products
(
    product_id INT(2) NOT NULL AUTO_INCREMENT,
    name VARCHAR(32),
    description VARCHAR(256),
    image_link VARCHAR(128),
    price DOUBLE,
    product_type INT(2),
    CONSTRAINT pk_product_id PRIMARY KEY (product_id),
    CONSTRAINT fk_product_type FOREIGN KEY (product_type)
        REFERENCES product_type(type_id)
);

CREATE TABLE product_type
(
    type_id INT(2) NOT NULL AUTO_INCREMENT,
    type VARCHAR(16),
    CONSTRAINT pk_product_type PRIMARY KEY (type_id)
);

CREATE TABLE orders
(
    order_id INT(4) NOT NULL AUTO_INCREMENT,
    user_id INT(3),
    CONSTRAINT pk_order_id PRIMARY KEY (order_id),
    CONSTRAINT fk_user_id FOREIGN KEY (user_id)
        REFERENCES users(user_id)
);

CREATE TABLE order_product_link
(
    order_id INT(3),
    product_id INT(2),
    quantity INT(2),
    CONSTRAINT pk_order_link PRIMARY KEY (order_id, product_id)
);
