SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `memakeit` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `memakeit`;


-- -----------------------------------------------------
-- Table `memakeit`.`mmi_pingbacks`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `memakeit`.`mmi_pingbacks` ;

CREATE  TABLE IF NOT EXISTS `memakeit`.`mmi_pingbacks` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `success` TINYINT(1) NOT NULL DEFAULT 0 ,
  `type` ENUM('pingback','trackback') NOT NULL DEFAULT 'pingback' ,
  `url_xmlrpc` VARCHAR(255) NOT NULL ,
  `url_from` VARCHAR(255) NOT NULL ,
  `url_to` VARCHAR(255) NOT NULL ,
  `post_data` TEXT NULL COMMENT 'serialized' ,
  `http_status_code` SMALLINT(6) UNSIGNED NOT NULL DEFAULT 200 ,
  `content_type` VARCHAR(255) NOT NULL DEFAULT 'text/xml' ,
  `error_num` MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT 0 ,
  `error_msg` VARCHAR(255) NOT NULL DEFAULT '' ,
  `response` TEXT NULL COMMENT 'serialized' ,
  `http_headers` TEXT NULL COMMENT 'serialized' ,
  `curl_info` TEXT NULL COMMENT 'serialized' ,
  `curl_options` TEXT NULL COMMENT 'serialized' ,
  `date_created` INT(11) UNSIGNED NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`id`) ,
  INDEX `IDX_url_from` (`url_from` ASC) ,
  INDEX `IDX_url_to` (`url_to` ASC) )
ENGINE = MyISAM;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
