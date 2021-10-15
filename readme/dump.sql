-- --------------------------------------------------------
-- Servidor:                     localhost
-- Versão do servidor:           10.4.21-MariaDB - mariadb.org binary distribution
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Copiando estrutura do banco de dados para projeto_audax
CREATE DATABASE IF NOT EXISTS `projeto_audax` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `projeto_audax`;

-- Copiando estrutura para tabela projeto_audax.perfil
CREATE TABLE IF NOT EXISTS `perfil` (
  `codigo` bigint(20) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(255) NOT NULL,
  `ativo` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `descricao` (`descricao`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- Copiando dados para a tabela projeto_audax.perfil: ~3 rows (aproximadamente)
/*!40000 ALTER TABLE `perfil` DISABLE KEYS */;
INSERT INTO `perfil` (`codigo`, `descricao`, `ativo`) VALUES
	(1, 'Administrador', 1),
	(2, 'Solicitador', 1),
	(3, 'Aprovador', 1);
/*!40000 ALTER TABLE `perfil` ENABLE KEYS */;

-- Copiando estrutura para tabela projeto_audax.perfil_telas
CREATE TABLE IF NOT EXISTS `perfil_telas` (
  `codigo` bigint(20) NOT NULL AUTO_INCREMENT,
  `perfil` bigint(20) NOT NULL DEFAULT 0,
  `tela` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

-- Copiando dados para a tabela projeto_audax.perfil_telas: ~7 rows (aproximadamente)
/*!40000 ALTER TABLE `perfil_telas` DISABLE KEYS */;
INSERT INTO `perfil_telas` (`codigo`, `perfil`, `tela`) VALUES
	(1, 1, 1),
	(2, 2, 1),
	(3, 3, 1),
	(4, 1, 2),
	(5, 1, 3),
	(6, 1, 4),
	(7, 1, 5);
/*!40000 ALTER TABLE `perfil_telas` ENABLE KEYS */;

-- Copiando estrutura para tabela projeto_audax.sistema_telas
CREATE TABLE IF NOT EXISTS `sistema_telas` (
  `codigo` bigint(20) NOT NULL AUTO_INCREMENT,
  `menu` varchar(150) NOT NULL,
  `dir` varchar(150) NOT NULL,
  `ordem` int(6) NOT NULL DEFAULT 1,
  `ativo` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `dir` (`dir`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- Copiando dados para a tabela projeto_audax.sistema_telas: ~4 rows (aproximadamente)
/*!40000 ALTER TABLE `sistema_telas` DISABLE KEYS */;
INSERT INTO `sistema_telas` (`codigo`, `menu`, `dir`, `ordem`, `ativo`) VALUES
	(1, 'Inicio', 'operacional/inicio.html', 0, 1),
	(2, 'Usuários', 'usuario/cadastrar.html', 1, 1),
	(3, 'Usuários', 'usuario/consultar.html', 2, 1),
	(4, 'Materiais', 'material/consultar.html', 4, 1),
	(5, 'Materiais', 'material/cadastrar.html', 3, 1);
/*!40000 ALTER TABLE `sistema_telas` ENABLE KEYS */;

-- Copiando estrutura para tabela projeto_audax.usuario
CREATE TABLE IF NOT EXISTS `usuario` (
  `codigo` bigint(20) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(120) NOT NULL,
  `perfil` bigint(20) DEFAULT 0,
  `senha` varchar(255) NOT NULL,
  `cpf` varchar(16) DEFAULT NULL,
  `telefone` varchar(15) DEFAULT NULL,
  `ativo` int(1) DEFAULT 1,
  `data_hora_cadastro` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Copiando dados para a tabela projeto_audax.usuario: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` (`codigo`, `nome`, `email`, `perfil`, `senha`, `cpf`, `telefone`, `ativo`, `data_hora_cadastro`) VALUES
	(1, 'Luiz Henrique', 'admin', 1, '123456', NULL, NULL, 1, '2021-10-09 08:33:02');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
