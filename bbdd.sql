DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`
(
    `id`         int unsigned   NOT NULL AUTO_INCREMENT,
    `email`      varchar(255)   NOT NULL DEFAULT '',
    `password`   varchar(255)   NOT NULL DEFAULT '',
    `birthdate`  date                    DEFAULT NULL,
    `phone`      varchar(20)    NOT NULL DEFAULT '',
    `balance`    decimal(18, 2) NOT NULL DEFAULT '20.00',
    `iban`       varchar(255)   NOT NULL DEFAULT '',
    `owner`      varchar(255)   NOT NULL DEFAULT '',
    `created_at` datetime       NOT NULL,
    `updated_at` datetime       NOT NULL,
    `active`     bit(1)                  DEFAULT b'0',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `tokens`;
CREATE TABLE `tokens`
(
    `id`     int          NOT NULL,
    `token`  varchar(255) NOT NULL DEFAULT '',
    `active` char         NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions`
(
    `id`         int unsigned   NOT NULL AUTO_INCREMENT,
    `senderid`   int            NOT NULL,
    `receiverid` int            NOT NULL,
    `amount`     decimal(18, 2) NOT NULL,
    `datetrans`  datetime       NOT NULL,
    `payed`      bit(1) DEFAULT b'0',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;