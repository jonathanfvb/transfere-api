CREATE DATABASE `transfere` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;

CREATE TABLE `transfere`.`user` (
  `uuid` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cpf` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  `cnpj` varchar(14) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `pass` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uuid`),
  UNIQUE KEY `unq_user_email` (`email`),
  UNIQUE KEY `unq_user_cpf_cnpj` (`cpf`,`cnpj`),
  UNIQUE KEY `unq_user_cnpj` (`cnpj`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `transfere`.`user_wallet` (
  `user_uuid` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `balance` decimal(14,2) NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`user_uuid`),
  CONSTRAINT `fk_user_wallet_user1` FOREIGN KEY (`user_uuid`) REFERENCES `user` (`uuid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `transfere`.`transaction` (
  `uuid` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `ammount` decimal(14,2) NOT NULL,
  `status_authorization` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `status_notification` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `user_payer_uuid` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `user_payee_uuid` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uuid`),
  KEY `fk_transaction_payer_idx` (`user_payer_uuid`),
  KEY `fk_transaction_payee_idx` (`user_payee_uuid`),
  CONSTRAINT `fk_transaction_user1` FOREIGN KEY (`user_payer_uuid`) REFERENCES `user` (`uuid`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_transaction_user2` FOREIGN KEY (`user_payee_uuid`) REFERENCES `user` (`uuid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
