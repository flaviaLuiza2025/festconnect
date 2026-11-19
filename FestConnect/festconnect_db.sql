-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 19/11/2026 às 14:38
-- Versão do servidor: 8.4.7
-- Versão do PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `festconnect_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `avaliacoes`
--

DROP TABLE IF EXISTS `avaliacoes`;
CREATE TABLE IF NOT EXISTS `avaliacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int NOT NULL,
  `profissional_id` int NOT NULL,
  `nota` int DEFAULT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `profissional_id` (`profissional_id`)
) ;

--
-- Despejando dados para a tabela `avaliacoes`
--

INSERT INTO `avaliacoes` (`id`, `cliente_id`, `profissional_id`, `nota`, `comentario`, `criado_em`) VALUES
(1, 1, 1, 4, 'serviço maravilhos, linda voz', '2026-10-22 13:26:58'),
(2, 1, 1, 4, 'serviço maravilhos, linda voz', '2026-10-22 13:43:50'),
(3, 1, 1, 5, 'amei', '2026-10-22 13:44:17'),
(4, 1, 4, 5, 'show', '2026-10-29 13:37:06');

-- --------------------------------------------------------

--
-- Estrutura para tabela `favoritos`
--

DROP TABLE IF EXISTS `favoritos`;
CREATE TABLE IF NOT EXISTS `favoritos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int NOT NULL,
  `profissional_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fav_unique` (`cliente_id`,`profissional_id`),
  KEY `profissional_id` (`profissional_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `galeria_profissionais`
--

DROP TABLE IF EXISTS `galeria_profissionais`;
CREATE TABLE IF NOT EXISTS `galeria_profissionais` (
  `id` int NOT NULL AUTO_INCREMENT,
  `profissional_id` int NOT NULL,
  `caminho_imagem` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_upload` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `profissional_id` (`profissional_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `galeria_profissionais`
--

INSERT INTO `galeria_profissionais` (`id`, `profissional_id`, `caminho_imagem`, `titulo`, `data_upload`) VALUES
(1, 3, 'uploads/25c7276ed05aac72222e1b9635ca9c64.jpg', '', '2026-10-22 13:24:23'),
(2, 3, 'uploads/38dae51f41d13f9cc4e85cdae473247f.jpg', '', '2026-10-22 13:24:40'),
(3, 3, 'uploads/649d4a5fc40377e96bfc02e3c3f613d9.jpg', '', '2026-10-22 13:45:19'),
(4, 3, 'uploads/915a3d1b08dc381128ebbaceb0bf8d61.jpg', '', '2026-10-22 13:45:19'),
(5, 3, 'uploads/dd5345bc6d547317f2b23cc9db1c8c7b.jpg', '', '2026-10-22 13:45:19'),
(6, 3, 'uploads/bd43f1302273b1c871147d9fdc315d4f.jpg', '', '2026-10-22 13:45:19'),
(7, 1, 'uploads/6a0ba46d8aefebe317bf74855473cddd.jpg', 'ultimo show', '2026-10-29 13:52:47'),
(8, 1, 'uploads/0cc5a588bbc329311de965d490612702.jpg', '', '2026-10-29 13:59:00'),
(9, 1, 'uploads/4001fa3f43f5e63284140b45a0137fd7.jpg', '', '2026-10-29 15:15:41'),
(10, 1, 'uploads/cfa554d592c4a70fb215d465f2b1ba56.jpg', '', '2026-10-29 15:19:16'),
(11, 1, 'uploads/ded9bf8302b1eb442838eb1ada48ce20.jpg', '', '2026-10-29 15:19:48'),
(12, 1, 'uploads/30a133b8eb4470bdb3f503f1c1cda901.jpg', '', '2026-10-29 15:19:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `orcamencos`
--

DROP TABLE IF EXISTS `orcamencos`;
CREATE TABLE IF NOT EXISTS `orcamencos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int NOT NULL,
  `profissional_id` int NOT NULL,
  `data_evento` date NOT NULL,
  `tipo_evento` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensagem` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `resposta_profissional` text COLLATE utf8mb4_unicode_ci,
  `valor_proposto` decimal(10,2) DEFAULT NULL,
  `status` enum('pendente','aceito','recusado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `profissional_id` (`profissional_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `orcamencos`
--

INSERT INTO `orcamencos` (`id`, `cliente_id`, `profissional_id`, `data_evento`, `tipo_evento`, `mensagem`, `resposta_profissional`, `valor_proposto`, `status`, `criado_em`) VALUES
(1, 1, 1, '2026-10-10', 'casamento', 'sdasdsa', 'fghfgh', 1400.00, 'aceito', '2026-09-28 01:30:42'),
(2, 1, 2, '2026-10-30', 'festa de 15 anos', 'solicitação de orçamento', NULL, NULL, 'pendente', '2026-10-01 13:22:01'),
(3, 1, 1, '2026-12-19', 'festa de 15 anos', 'errfe', NULL, NULL, 'pendente', '2026-10-11 23:06:05');

-- --------------------------------------------------------

--
-- Estrutura para tabela `perfis_profissionais`
--

DROP TABLE IF EXISTS `perfis_profissionais`;
CREATE TABLE IF NOT EXISTS `perfis_profissionais` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `categoria` enum('DJ','Banda','Musico Solo','Duo','Fotografo','Tecnico de Som','Iluminacao','Cerimonialista') COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `localizacao` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `faixa_preco` decimal(10,2) NOT NULL DEFAULT '0.00',
  `contato_whatsapp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `perfis_profissionais`
--

INSERT INTO `perfis_profissionais` (`id`, `usuario_id`, `categoria`, `descricao`, `localizacao`, `faixa_preco`, `contato_whatsapp`) VALUES
(1, 2, 'Musico Solo', 'musico guitarrista', 'santos', 2600.00, '13996124062'),
(2, 4, 'Duo', 'sfdjskjfskajgkjiajrtijsdijfklsdjafkjaskldjf', 'santos', 1600.00, '13996124062'),
(3, 5, 'Tecnico de Som', '', 'Santos', 3100.00, '13991134848'),
(4, 7, 'Fotografo', 'ekmfdkfmçdsmfçdkffm', 'santos', 2100.00, '13991134846');

-- --------------------------------------------------------

--
-- Estrutura para tabela `portfolio`
--

DROP TABLE IF EXISTS `portfolio`;
CREATE TABLE IF NOT EXISTS `portfolio` (
  `id` int NOT NULL AUTO_INCREMENT,
  `profissional_id` int NOT NULL,
  `tipo` enum('foto','video') COLLATE utf8mb4_unicode_ci NOT NULL,
  `url_midia` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `profissional_id` (`profissional_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('cliente','profissional') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cliente',
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `criado_em`) VALUES
(1, 'flavia silva', 'flpsiva4.1@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$VHY2VVB0UVRzZmVlNHZBcw$ru/cUMIHA/7pomEWvLdV6WtgADK4O8ih31ZWx4Vg8qs', 'cliente', '2026-09-28 00:38:20'),
(2, 'wil', 'wil@wil.com', '$argon2id$v=19$m=65536,t=4,p=1$WEk0T2wxOURwdTNJRnlGaA$Xk8M94fACpldwiNRP2DMAN1RMLIkdBEkbUmyxcFG7O8', 'profissional', '2026-09-28 00:39:10'),
(3, 'maria', 'maria@maria.com', '$argon2id$v=19$m=65536,t=4,p=1$UjF4bWh1THFqR05RVDNpbw$w7fw6MaRv4BBpRUWbdfp4eCPUZrcnk3fBLQj24SSzXs', 'profissional', '2026-09-29 22:30:00'),
(4, 'joão', 'jo@jo.com', '$argon2id$v=19$m=65536,t=4,p=1$OEhiTmZvck10NVJ5TFFPWA$SMOxHxFgwkI6sf3gkahceRIEEd3qVgAkpRZmStpwluw', 'profissional', '2026-10-01 13:09:56'),
(5, 'tato', 'tato@tato.com', '$argon2id$v=19$m=65536,t=4,p=1$ZnM4dVlsR2RRVW0waDQxdg$VP3CC2ZlmagHlAQ6uM4/bYzHmdCl3xQ8//+4Ay3kcHw', 'profissional', '2026-10-22 13:02:39'),
(6, 'ana', 'ana@ana.com', '$argon2id$v=19$m=65536,t=4,p=1$SHB2VnY0M0tFdVZ0Zm0uZA$mi9eXjTlpvQi9UxXfhPwSXw19SZHBEPb38NJJv9Ebm8', 'profissional', '2026-10-29 13:22:18'),
(7, 'pedro', 'pedro@pedro.com', '$argon2id$v=19$m=65536,t=4,p=1$dWZxOS90VzlDb1RpU09XeA$MtghwOT384584m1fRqHOz7W9H/JEmLW05nMms0zPaCE', 'profissional', '2026-10-29 13:35:04');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
