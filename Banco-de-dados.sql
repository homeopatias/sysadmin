-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 11, 2015 at 10:35 PM
-- Server version: 5.5.41-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `homeopatias`
--

-- --------------------------------------------------------

--
-- Table structure for table `Administrador`
--

CREATE TABLE IF NOT EXISTS `Administrador` (
  `idAdmin` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico do administrador',
  `idUsuario` int(11) NOT NULL COMMENT 'Identificador único do usuário que esse administrador representa',
  `nivel` enum('professor','coordenador','administrador') NOT NULL COMMENT 'Nivel de privilegio desse administrador no sistema',
  `corrigeTrabalho` tinyint(1) NOT NULL COMMENT 'Caso esse administrador seja um professor, determina se ele pode corrigir trabalhos ou não',
  `permissoes` int(5) NOT NULL DEFAULT '0' COMMENT 'Bitflag de acesso de admins',
  PRIMARY KEY (`idAdmin`),
  KEY `idUsuario` (`idUsuario`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Administradores do sistema' AUTO_INCREMENT=12 ;

--
-- Dumping data for table `Administrador`
--

INSERT INTO `Administrador` (`idAdmin`, `idUsuario`, `nivel`, `corrigeTrabalho`, `permissoes`) VALUES
(1, 1, 'administrador', 0, 63),
(2, 16, 'coordenador', 0, 0),
(3, 17, 'coordenador', 0, 0),
(4, 18, 'coordenador', 0, 0),
(5, 19, 'professor', 1, 0),
(6, 20, 'professor', 0, 0),
(7, 21, 'professor', 1, 0),
(8, 23, 'coordenador', 0, 0),
(9, 24, 'professor', 1, 0),
(10, 25, 'administrador', 0, 47),
(11, 26, 'coordenador', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `Aluno`
--

CREATE TABLE IF NOT EXISTS `Aluno` (
  `numeroInscricao` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Numero de inscricao do aluno',
  `idUsuario` int(11) NOT NULL COMMENT 'Identificador único do usuário que esse aluno representa',
  `status` enum('preinscrito','inscrito','desistente','formado','inativo') NOT NULL COMMENT 'Status desse aluno (pré-inscrito, inscrito, etc)',
  `idIndicador` int(11) DEFAULT NULL COMMENT 'Numero de inscricao do aluno que indicou esse aluno, caso aplicavel',
  `telefone` text COMMENT 'Telefone do aluno',
  `escolaridade` enum('fundamental incompleto','fundamental completo','médio incompleto','médio completo','superior incompleto','superior completo','mestrado','doutorado') DEFAULT NULL COMMENT 'Nível de escolaridade do aluno',
  `curso` varchar(200) DEFAULT NULL COMMENT 'Curso que o aluno frequentou, caso esteja no nível superior ou acima',
  `cep` varchar(8) DEFAULT NULL COMMENT 'Código Postal do Aluno',
  `rua` varchar(255) DEFAULT NULL COMMENT 'Rua do Aluno',
  `numero` int(11) DEFAULT NULL COMMENT 'Numero do endereço do Aluno',
  `bairro` varchar(255) DEFAULT NULL COMMENT 'Bairro do Aluno',
  `complemento` varchar(255) DEFAULT NULL COMMENT 'Complemento do Endereço',
  `estado` varchar(2) DEFAULT NULL COMMENT 'Bairro em que o aluno reside',
  `cidade` varchar(255) DEFAULT NULL COMMENT 'Cidade em que o aluno reside',
  `pais` varchar(3) DEFAULT NULL COMMENT 'País que o aluno reside',
  `tipo_curso` enum('extensao','pos','instituto') NOT NULL COMMENT 'tipo de curso do aluno',
  `modalidade_curso` enum('regular','intensivo','','') NOT NULL DEFAULT 'regular',
  `tipo_cadastro` enum('instituto','faculdade inspirar') NOT NULL COMMENT 'tipo de cadastro do aluno',
  `ativo` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Determina se esse aluno está ativo (sempre verdadeiro para alunos da extensão, verdadeiro para alunos da extensão que já enviaram os documentos)',
  `recebeEmail` tinyint(1) NOT NULL COMMENT 'Determina se o aluno deseja receber e-mails do curso ou não',
  PRIMARY KEY (`numeroInscricao`),
  KEY `idIndicador` (`idIndicador`),
  KEY `idAluno` (`idUsuario`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Aluno do curso' AUTO_INCREMENT=27 ;

--
-- Dumping data for table `Aluno`
--

INSERT INTO `Aluno` (`numeroInscricao`, `idUsuario`, `status`, `idIndicador`, `telefone`, `escolaridade`, `curso`, `cep`, `rua`, `numero`, `bairro`, `complemento`, `estado`, `cidade`, `pais`, `tipo_curso`, `modalidade_curso`, `tipo_cadastro`, `ativo`, `recebeEmail`) VALUES
(1, 2, 'preinscrito', NULL, '1693018232', 'médio completo', NULL, '14890470', 'Rua João Merchiori', 963, 'Jaboticabal', '', 'SP', 'São Paulo', 'BRL', 'pos', 'regular', 'instituto', 1, 0),
(2, 3, 'inscrito', 1, '1961438378', 'superior completo', 'Ciências Contábeis', '13098603', 'Rua Argeu Pires Neto', 149, 'Santa Amélia', 'Apto 400', 'SP', 'Campinas', 'BRL', 'extensao', 'regular', 'instituto', 1, 0),
(3, 4, 'preinscrito', NULL, '8260134527', 'médio completo', NULL, '57600830', 'Rua Coronel Antônio Pantaleão', 563, 'Monteiro Lobato', 'Apto 501, Bloco B', 'AL', 'Palmeira dos Índios', 'BRL', 'extensao', 'regular', 'instituto', 1, 0),
(4, 5, 'preinscrito', NULL, '6135342360', 'fundamental incompleto', NULL, '70645120', 'Quadra SRES Quadra 10', 1567, 'Maria José', 'Bloco L', 'DF', 'Cruzeiro', 'BRL', 'extensao', 'regular', 'instituto', 1, 0),
(5, 6, 'preinscrito', 4, '8698463979', 'fundamental incompleto', NULL, '64082670', 'Rua Laira', 715, 'Santa Mônica', '', 'PI', 'Teresina', 'BRL', 'extensao', 'regular', 'instituto', 1, 0),
(6, 7, 'preinscrito', NULL, '2169357517', 'fundamental incompleto', NULL, '21735110', 'Rua Professor Carvalho e Melo', 1856, 'Ottawa', '', 'RJ', 'Rio de Janeiro', 'BRL', 'extensao', 'regular', 'instituto', 1, 0),
(7, 8, 'inscrito', NULL, '1184439221', 'fundamental incompleto', NULL, '31314333', 'Avenida São Paulo', 909, 'Hortêncio', 'Bloco A, Apto. 289', 'SP', 'Piracicaba', 'BRL', 'extensao', 'regular', 'instituto', 1, 0),
(8, 9, 'inscrito', NULL, '8498876543', 'doutorado', 'Astrofísica quântica', '45543398', 'Rua Madagascar', 883, 'Alabama', '', 'RN', 'Taboleiro Grande', 'BRL', 'extensao', 'regular', 'instituto', 1, 0),
(9, 10, 'inscrito', NULL, '5787659485', 'fundamental incompleto', NULL, '67754390', 'Rua dos Japoneses', 394, 'Violeta', '', 'AP', 'Macapá', 'BRL', 'extensao', 'regular', 'instituto', 1, 0),
(10, 11, 'inscrito', 11, '2098764959', 'fundamental incompleto', NULL, '98983399', 'Rua Almenara', 874, 'Jorema', '', 'GO', 'Goiânia', 'BRL', 'extensao', 'regular', 'instituto', 1, 0),
(11, 12, 'inscrito', 9, '3498123232', 'fundamental completo', NULL, '88744596', 'Avenida Silveira', 111, 'Capanema', '', 'MG', 'Uberlândia', 'BRL', 'extensao', 'regular', 'instituto', 1, 0),
(12, 27, 'preinscrito', NULL, '3122334455', 'superior completo', 'Abacate', '33884555', 'TWOOOOO', 89, 'So needless to say', 'Say after me', 'AC', 'Don''t let away', '', '', 'regular', '', 1, 0),
(13, 28, 'preinscrito', NULL, '3399448855', 'superior incompleto', 'abastece', '30495454', 'The birds and the bees', 39, 'Sowing the seeds', '', 'AC', 'Derpity derp', '', 'extensao', 'regular', 'faculdade inspirar', 1, 0),
(14, 29, 'preinscrito', NULL, '3129394939', 'fundamental incompleto', NULL, '93945444', 'tesste', 34, 'marracuda', '', 'AC', 'macarruda', 'BRL', 'extensao', 'regular', 'faculdade inspirar', 1, 0),
(15, 30, 'preinscrito', NULL, NULL, 'superior completo', 'Farmácia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'BRL', 'pos', 'regular', 'faculdade inspirar', 1, 0),
(16, 31, 'preinscrito', NULL, '9992929292', 'fundamental incompleto', NULL, '12345543', 'Armin', 12, 'Bairro', '', 'AC', 'Cidade', 'BRL', 'extensao', 'regular', 'faculdade inspirar', 1, 0),
(17, 32, 'preinscrito', NULL, '3112344321', 'fundamental incompleto', NULL, '14890470', 'teste', 21, 'teste', '', 'MG', 'teste', 'BRL', 'extensao', 'regular', 'faculdade inspirar', 1, 0),
(18, 33, 'preinscrito', NULL, '3112344321', 'fundamental incompleto', NULL, '14890470', 'teste', 12, 'teste', '', 'MG', 'teste', 'BRL', 'extensao', 'regular', 'faculdade inspirar', 1, 0),
(19, 34, 'preinscrito', NULL, '3112344321', 'fundamental incompleto', NULL, '14890470', 'teste', 21, 'teste', '', 'MG', 'teste', 'BRL', 'extensao', 'regular', 'faculdade inspirar', 1, 0),
(20, 35, 'preinscrito', NULL, '3112344321', 'fundamental incompleto', NULL, '14890470', 'teste', 21, 'teste', '', 'MG', 'teste', 'BRL', 'extensao', 'regular', 'faculdade inspirar', 1, 0),
(21, 36, 'preinscrito', NULL, '3112344321', 'fundamental incompleto', NULL, '14890470', 'teste', 21, 'teste', '', 'MG', 'teste', 'BRL', 'extensao', 'regular', 'faculdade inspirar', 1, 0),
(22, 37, 'preinscrito', NULL, '3112344321', 'fundamental incompleto', NULL, '14890470', 'teste', 21, 'teste', '', 'MG', 'teste', 'BRL', 'extensao', 'regular', 'faculdade inspirar', 1, 0),
(23, 38, 'preinscrito', NULL, '3112344321', 'fundamental incompleto', NULL, '14890470', 'teste', 21, 'teste', '', 'MG', 'teste', 'BRL', 'extensao', 'regular', 'faculdade inspirar', 1, 0),
(24, 39, 'preinscrito', NULL, '3112344321', 'superior completo', 'Homepatias', '14890470', 'teste', 21, 'teste', '', 'MG', 'teste', 'BRL', 'pos', 'regular', 'faculdade inspirar', 1, 0),
(25, 41, 'preinscrito', NULL, '3112344321', 'fundamental incompleto', NULL, '14890470', 'adsf', 21, 'adsfasdf', '', 'AC', 'adsf', 'BRL', 'extensao', 'regular', 'faculdade inspirar', 1, 0),
(26, 42, 'preinscrito', NULL, '3112344321', NULL, NULL, '14890470', 'asdf', 21, 'adf', '', 'AC', 'adsf', 'BRL', 'instituto', 'regular', 'instituto', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `Artigo`
--

CREATE TABLE IF NOT EXISTS `Artigo` (
  `idArtigo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico',
  `autor` varchar(100) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `conteudo` text NOT NULL,
  `dataPublic` datetime NOT NULL COMMENT 'Data de publicacao do artigo',
  `tipo` enum('artigo','noticia') NOT NULL COMMENT 'Determina se é um artigo ou notícia',
  PRIMARY KEY (`idArtigo`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Artigo ou noticia a ser mostrada no site' AUTO_INCREMENT=5 ;

--
-- Dumping data for table `Artigo`
--

INSERT INTO `Artigo` (`idArtigo`, `autor`, `titulo`, `conteudo`, `dataPublic`, `tipo`) VALUES
(1, 'Luan Antônio', 'Homeopatia para doenças crônicas', 'Podemos dizer que no cenário atual de [...]', '2014-11-13 13:03:15', 'artigo'),
(2, 'João Fernando', 'Uso de homeopatia para causas globais', 'A homeopatia tem a utilidade de [...]', '2014-11-13 13:04:11', 'artigo'),
(3, 'Elaine Souza', 'Frutas para tratamento da gripe', 'As frutas [...]', '2014-11-13 13:04:42', 'artigo'),
(4, 'João Guedes', 'Abertas as inscrições para 2015', 'As incrições para o curso de homeopatia estão abertas para [...]', '2014-11-13 13:08:09', 'noticia');

-- --------------------------------------------------------

--
-- Table structure for table `Associado`
--

CREATE TABLE IF NOT EXISTS `Associado` (
  `idAssoc` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico do associado',
  `idUsuario` int(11) NOT NULL COMMENT 'Identificador único do usuário que esse associado representa',
  `instituicao` enum('atenemg','conahom') NOT NULL COMMENT 'Nome da instituicao associada',
  `formacaoTerapeutica` varchar(200) NOT NULL COMMENT 'Formação terapêutica desse associado',
  `telefone` text NOT NULL COMMENT 'Telefone do associado',
  `endereco` varchar(200) NOT NULL COMMENT 'Endereco completo do associado',
  `cidade` varchar(100) NOT NULL COMMENT 'Cidade de residência do associado',
  `estado` text NOT NULL COMMENT 'UF de residência do associado',
  `numObjeto` varchar(100) DEFAULT NULL COMMENT 'Código da carteirinha no correio',
  `dataEnvioCarteirinha` date DEFAULT NULL COMMENT 'Data de envio da carteirinha',
  `enviouDocumentos` tinyint(1) NOT NULL COMMENT 'Determina se o associado já enviou os documentos necessários e foi aprovado',
  `cep` varchar(8) NOT NULL COMMENT 'Código Postal do Associado',
  `rua` varchar(255) NOT NULL COMMENT 'Rua do associado',
  `numero` int(11) NOT NULL COMMENT 'Numero do endereço do Associado',
  `bairro` varchar(255) NOT NULL COMMENT 'Bairro do Associado',
  `complemento` varchar(255) DEFAULT NULL COMMENT 'Complemento do Endereço',
  `pais` varchar(3) NOT NULL COMMENT 'País que o Associado reside',
  `desconto_individual` float NOT NULL DEFAULT '0' COMMENT 'Desconto individual para o associado',
  PRIMARY KEY (`idAssoc`),
  KEY `idUsuario` (`idUsuario`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Associado da CONAHOM/ATENEMG' AUTO_INCREMENT=5 ;

--
-- Dumping data for table `Associado`
--

INSERT INTO `Associado` (`idAssoc`, `idUsuario`, `instituicao`, `formacaoTerapeutica`, `telefone`, `endereco`, `cidade`, `estado`, `numObjeto`, `dataEnvioCarteirinha`, `enviouDocumentos`, `cep`, `rua`, `numero`, `bairro`, `complemento`, `pais`, `desconto_individual`) VALUES
(1, 13, 'conahom', 'Quiropraxia', '2487348942', '', 'Nova Lima', 'MG', NULL, NULL, 1, '43857654', 'Rua Nogueira', 98, 'Carijós', '', 'BRL', 0),
(2, 14, 'atenemg', 'Florais', '2398575677', '', 'Belém', 'PA', NULL, NULL, 0, '30843030', 'Rua Lobo Soares', 87, 'Jordânia', '', 'BRL', 0),
(3, 15, 'conahom', 'Tratamentos de longo prazo', '3372383294', '', 'Palmas', 'TO', NULL, NULL, 0, '44556544', 'Rua das Flores', 83, 'Especial', '', 'BRL', 0),
(4, 22, 'atenemg', 'Homeopatia do sono', '3134921312', '', 'Belo Horizonte', 'MG', NULL, NULL, 0, '31540120', 'Rua Professor Clóvis de Faria', 103, 'Santa Amélia', 'Apto. 201', 'BRL', 0);

-- --------------------------------------------------------

--
-- Table structure for table `Aula`
--

CREATE TABLE IF NOT EXISTS `Aula` (
  `idAula` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico de aula',
  `chaveCidade` int(11) NOT NULL COMMENT 'Identificador da cidade onde ocorrera essa aula',
  `etapa` int(11) NOT NULL COMMENT 'Numero da etapa a qual essa aula se refere',
  `data` datetime NOT NULL COMMENT 'Data e horario da aula',
  `idProfessor` int(11) DEFAULT NULL COMMENT 'Identificador único do professor que ministrara a aula',
  `nota` float DEFAULT NULL COMMENT 'Media das notas dadas a essa aula pelos alunos',
  `descricao` varchar(10000) NOT NULL COMMENT 'Descrição do conteúdo que será dado nessa aula',
  PRIMARY KEY (`idAula`),
  KEY `chaveCidade` (`chaveCidade`),
  KEY `idProfessor` (`idProfessor`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Aula lancada no sistema' AUTO_INCREMENT=14 ;

--
-- Dumping data for table `Aula`
--

INSERT INTO `Aula` (`idAula`, `chaveCidade`, `etapa`, `data`, `idProfessor`, `nota`, `descricao`) VALUES
(1, 1, 1, '2014-03-03 12:30:00', 5, NULL, 'Introdução à Homeopatia'),
(2, 1, 2, '2014-04-03 12:30:00', 5, NULL, 'Continuando as bases da alopatia'),
(3, 2, 1, '2014-06-05 14:30:00', 7, NULL, 'Conceitos homeopáticos básicos'),
(4, 2, 1, '2014-09-03 08:30:00', 7, NULL, 'Conclusões da etapa'),
(5, 2, 1, '2014-12-10 09:00:00', 7, NULL, 'Guia de estudos para as férias'),
(6, 2, 3, '2014-03-03 08:00:00', 7, NULL, 'Continuação dos estudos sobre florais'),
(7, 2, 4, '2014-07-05 17:00:00', 7, NULL, 'Grandes homeopatas da história'),
(8, 1, 1, '2014-12-18 09:00:00', 5, NULL, 'Finalização de curso'),
(9, 2, 1, '2014-12-18 09:00:00', 7, NULL, 'Finalização de curso'),
(10, 3, 1, '2014-12-18 09:00:00', 6, NULL, 'Finalização de curso'),
(11, 1, 1, '2014-11-19 09:00:00', 7, NULL, 'Aula prática de técnicas homeopáticas.'),
(12, 4, 1, '2014-02-02 12:30:00', 5, 44.4444, 'Aula introdutória'),
(13, 4, 1, '2014-09-05 08:10:00', 5, 66.6667, 'Aula final');

-- --------------------------------------------------------

--
-- Table structure for table `Cidade`
--

CREATE TABLE IF NOT EXISTS `Cidade` (
  `idCidade` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico de cidade',
  `UF` text NOT NULL,
  `ano` int(10) unsigned NOT NULL COMMENT 'Ano para o qual essa oferta do curso vale',
  `nome` varchar(100) NOT NULL,
  `idCoordenador` int(11) NOT NULL COMMENT 'Identificador unico do coordenador dessa cidade',
  `local` varchar(200) NOT NULL,
  `precoInscricao` float NOT NULL COMMENT 'Valor a ser pago para a inscrição nessa cidade',
  `precoParcela` float NOT NULL COMMENT 'Valor da parcela a ser pago mensalmente por alunos dessa cidade',
  `limiteInscricao` date NOT NULL COMMENT 'Data limite para matrícula nessa cidade',
  `nomeEmpresa` varchar(100) NOT NULL COMMENT 'Nome da empresa responsável por essa cidade',
  `cnpjEmpresa` char(14) NOT NULL COMMENT 'CNPJ da empresa responsável por essa cidade',
  `custoCurso` float NOT NULL COMMENT 'valor de custo para abertura do curso',
  `tipo_curso` enum('extensao','pos','instituto','todos') NOT NULL COMMENT 'Tipos de curso oferecidos na cidade',
  `modalidadeCidade` enum('regular','intensivo','ambos','') NOT NULL COMMENT 'Modalidades disponíveis na cidade',
  `cadastro_ativo` tinyint(4) NOT NULL COMMENT 'Bool que indica se o cadastro da cidade está ativo ou não',
  `parcela_extensao_regular` float NOT NULL DEFAULT '0' COMMENT 'Preço da inscrição de extensão na cidade',
  `inscricao_extensao_regular` float NOT NULL DEFAULT '0' COMMENT 'Preço da parcela de extensão na cidade',
  `parcela_extensao_intensivo` float NOT NULL DEFAULT '0',
  `inscricao_extensao_intensivo` float NOT NULL DEFAULT '0',
  `parcela_pos_regular` float NOT NULL DEFAULT '0' COMMENT 'Preço da inscrição de pos na cidade',
  `inscricao_pos_regular` float NOT NULL DEFAULT '0' COMMENT 'Preço da parcela de pos na cidade',
  `parcela_pos_intensivo` float NOT NULL DEFAULT '0',
  `inscricao_pos_intensivo` float NOT NULL DEFAULT '0',
  `inscricao_instituto_regular` float NOT NULL DEFAULT '0',
  `parcela_instituto_regular` float NOT NULL DEFAULT '0',
  `inscricao_instituto_intensivo` float NOT NULL DEFAULT '0',
  `parcela_instituto_intensivo` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`idCidade`),
  KEY `idCoordenador` (`idCoordenador`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Oferta de curso em determinada cidade em determinado período' AUTO_INCREMENT=20 ;

--
-- Dumping data for table `Cidade`
--

INSERT INTO `Cidade` (`idCidade`, `UF`, `ano`, `nome`, `idCoordenador`, `local`, `precoInscricao`, `precoParcela`, `limiteInscricao`, `nomeEmpresa`, `cnpjEmpresa`, `custoCurso`, `tipo_curso`, `modalidadeCidade`, `cadastro_ativo`, `parcela_extensao_regular`, `inscricao_extensao_regular`, `parcela_extensao_intensivo`, `inscricao_extensao_intensivo`, `parcela_pos_regular`, `inscricao_pos_regular`, `parcela_pos_intensivo`, `inscricao_pos_intensivo`, `inscricao_instituto_regular`, `parcela_instituto_regular`, `inscricao_instituto_intensivo`, `parcela_instituto_intensivo`) VALUES
(1, 'MG', 2014, 'Belo Horizonte', 3, 'Faculdade de odontologia da UFMG', 100, 30, '2014-05-02', 'Homeobrás', '56667868000102', 7500.5, '', 'regular', 1, 150, 155, 0, 0, 150, 200, 0, 0, 0, 0, 0, 0),
(2, 'RJ', 2014, 'Rio de Janeiro', 2, 'Faculdade de odontologia da UFRJ', 90, 85, '2014-05-02', 'Homeobrás', '56667868000102', 6750, 'extensao', 'regular', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3, 'SP', 2014, 'São Paulo', 4, 'Faculdade de odontologia da USP', 120, 80, '2014-08-10', 'Homeobrás', '56667868000102', 6750, 'extensao', 'regular', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(4, 'MG', 2014, 'Sabará', 8, 'Faculdade de Sabará', 150, 220, '2014-11-20', 'Curso de Homeopatias sabará', '63323722000105', 8000, 'extensao', 'regular', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(5, 'AM', 2014, 'Manaus', 11, 'UFAM', 40, 100, '2013-10-10', 'Homeobrás', '56667868000102', 0, 'extensao', 'regular', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(6, 'MG', 2015, 'Belo Horizonte', 3, 'UFMG', 100, 80, '2015-05-05', 'Homeobrás', '63323722000105', 0, 'extensao', 'regular', 1, 200, 150, 0, 0, 120, 120, 0, 0, 0, 0, 0, 0),
(7, 'MG', 2014, 'Contagem', 3, 'Curso Homeopático Contagem', 300, 220, '2014-07-07', 'Curso Homeopático Contagem', '44732943000184', 0, 'extensao', 'regular', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(8, 'TO', 2014, 'Palmas', 3, 'UFT', 300, 170, '2014-02-11', 'UFT', '65563135000100', 0, 'extensao', 'regular', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(9, 'RN', 2014, 'Natal', 3, 'UFRN', 300, 220, '2014-03-12', 'UFRN', '27266655000162', 0, 'extensao', 'regular', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(10, 'PA', 2014, 'Belém', 3, 'Homeopatias Paraense', 250, 220, '2014-05-11', 'Homeopatias Paraense', '37156271000140', 0, 'extensao', 'regular', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(11, 'MG', 2015, 'Belo Horizonte', 3, 'Faculdade de odontologia da UFMG', 250, 220, '2015-03-05', 'Faculdade de odontologia da UFMG', '56667868000102', 0, 'extensao', 'regular', 1, 150, 300, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(12, 'MG', 2015, 'teste', 2, 'Homeos', 0, 0, '2015-03-15', 'Homep', '77974602000174', 15000, '', 'regular', 1, 150, 200, 0, 0, 200, 250, 0, 0, 0, 0, 0, 0),
(17, 'AC', 2015, 'Testópolis', 4, 'asdf', 0, 0, '2015-04-13', 'disabled', '00000000000000', 0, 'extensao', 'regular', 1, 50, 20, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(18, 'AC', 2015, 'Testando', 8, 'asdfas', 0, 0, '2015-04-30', 'disabled', '00000000000000', 0, 'extensao', 'regular', 1, 100, 250, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(19, 'MA', 2015, 'Institulandia', 11, 'adf', 0, 0, '2015-03-05', 'disabled', '00000000000000', 0, 'instituto', 'ambos', 1, 0, 0, 0, 0, 0, 0, 0, 0, 200, 150, 350, 300);

-- --------------------------------------------------------

--
-- Table structure for table `Compra`
--

CREATE TABLE IF NOT EXISTS `Compra` (
  `idCompra` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico de compra',
  `cpf` char(11) NOT NULL COMMENT 'CPF do comprador',
  `nome` varchar(100) NOT NULL,
  `data` date NOT NULL COMMENT 'Data da compra',
  `contato` varchar(15) NOT NULL,
  PRIMARY KEY (`idCompra`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Compra de produtos' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Evento`
--

CREATE TABLE IF NOT EXISTS `Evento` (
  `idEvento` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico',
  `dataPublic` datetime NOT NULL COMMENT 'Data de publicacao dos dados do evento no site',
  `dataEvento` datetime NOT NULL COMMENT 'Data em que ocorrera o evento',
  `titulo` varchar(100) NOT NULL,
  `local` varchar(500) NOT NULL,
  `descricao` varchar(3000) NOT NULL,
  PRIMARY KEY (`idEvento`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Dados de um evento a serem mostrados no site' AUTO_INCREMENT=3 ;

--
-- Dumping data for table `Evento`
--

INSERT INTO `Evento` (`idEvento`, `dataPublic`, `dataEvento`, `titulo`, `local`, `descricao`) VALUES
(1, '2014-11-13 13:06:24', '2015-10-10 07:30:00', 'Jornada Homeopática', 'Teatro sesiminas', 'Palestra com o professor [...]'),
(2, '2014-11-13 13:07:26', '2014-01-01 12:30:00', 'Encontro dos professores', 'Faculdade de Medicina da UFMG', 'Os alunos poderão encontrar os professores para discutir sobre [...]');

-- --------------------------------------------------------

--
-- Table structure for table `Frequencia`
--

CREATE TABLE IF NOT EXISTS `Frequencia` (
  `chaveAluno` int(11) NOT NULL DEFAULT '0' COMMENT 'Numero de inscricao do aluno ao qual essa frequencia se relaciona',
  `chaveAula` int(11) NOT NULL COMMENT 'Identificador da aula a qual essa frequencia se refere',
  `presenca` tinyint(1) NOT NULL COMMENT 'Representa se o aluno estava presente nessa aula ou nao',
  `jaAvaliou` tinyint(1) NOT NULL COMMENT 'Determina se o aluno já avaliou essa aula ou não',
  `aprovacaoPendente` tinyint(1) NOT NULL COMMENT 'Determina se essa frequência requer avaliação por parte de um administrador',
  `justificativaAusencia` varchar(10000) DEFAULT NULL COMMENT 'Justificativa pela ausência em um dia de aula',
  PRIMARY KEY (`chaveAula`,`chaveAluno`),
  KEY `chaveAluno` (`chaveAluno`),
  KEY `chaveAula` (`chaveAula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lancamento da presenca ou ausencia de um aluno em uma aula';

--
-- Dumping data for table `Frequencia`
--

INSERT INTO `Frequencia` (`chaveAluno`, `chaveAula`, `presenca`, `jaAvaliou`, `aprovacaoPendente`, `justificativaAusencia`) VALUES
(6, 12, 1, 1, 0, 'TESTE'),
(7, 12, 1, 1, 0, NULL),
(8, 12, 1, 1, 0, NULL),
(6, 13, 1, 1, 0, NULL),
(7, 13, 1, 1, 0, NULL),
(8, 13, 1, 1, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Instituicao`
--

CREATE TABLE IF NOT EXISTS `Instituicao` (
  `idInstituicao` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da instituição',
  `nome` enum('atenemg','conahom') NOT NULL COMMENT 'Nome da instituição',
  `valorInscricao` float NOT NULL COMMENT 'Preço da inscrição nessa instituição',
  `valorAnuidade` float NOT NULL COMMENT 'Preço da anuidade nessa instituição',
  `inicioInsc` datetime NOT NULL COMMENT 'Data a partir da qual os associados podem se associar',
  `fimInsc` datetime NOT NULL COMMENT 'Data até qual os associados podem se associar',
  `ano` int(11) NOT NULL COMMENT 'Ano para o qual as inscrições estão/estarão abertas',
  PRIMARY KEY (`idInstituicao`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Instituição na qual podem ser feitas as associações' AUTO_INCREMENT=3 ;

--
-- Dumping data for table `Instituicao`
--

INSERT INTO `Instituicao` (`idInstituicao`, `nome`, `valorInscricao`, `valorAnuidade`, `inicioInsc`, `fimInsc`, `ano`) VALUES
(1, 'atenemg', 30, 50, '2014-08-22 00:00:00', '2015-01-14 00:00:00', 2015),
(2, 'conahom', 40, 40, '2014-07-14 00:00:00', '2014-12-31 00:00:00', 2014);

-- --------------------------------------------------------

--
-- Table structure for table `Livro`
--

CREATE TABLE IF NOT EXISTS `Livro` (
  `idLivro` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico de livro',
  `valor` float NOT NULL COMMENT 'Preco do livro',
  `quantidade` int(10) unsigned NOT NULL COMMENT 'Quantidade do livro em estoque',
  `nome` varchar(500) NOT NULL,
  `autor` varchar(100) NOT NULL,
  `editora` varchar(100) NOT NULL,
  `dataPublic` date NOT NULL COMMENT 'Data da publicacao do livro',
  `edicao` int(10) unsigned NOT NULL COMMENT 'Numero da edicao do livro',
  `fornecedor` varchar(200) NOT NULL,
  PRIMARY KEY (`idLivro`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Livros a venda no sistema' AUTO_INCREMENT=3 ;

--
-- Dumping data for table `Livro`
--

INSERT INTO `Livro` (`idLivro`, `valor`, `quantidade`, `nome`, `autor`, `editora`, `dataPublic`, `edicao`, `fornecedor`) VALUES
(1, 15.9, 10, 'Dicionário da Homeopatia', 'Juliano Souza', 'Editora homeopática hannemanniana', '2015-10-20', 5, 'Fornecedoras S/A'),
(2, 12.2, 1000, 'Guia Homeopático', 'Kristian Robert', 'Homeopath', '1995-02-05', 7, 'Fornecedoras S/A');

-- --------------------------------------------------------

--
-- Table structure for table `Matricula`
--

CREATE TABLE IF NOT EXISTS `Matricula` (
  `idMatricula` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da matrícula feita por um aluno',
  `chaveAluno` int(11) NOT NULL COMMENT 'Identificador do aluno ao qual essa matrícula se refere',
  `etapa` int(10) unsigned NOT NULL COMMENT 'Etapa a qual essa matrícula se refere',
  `aprovado` tinyint(1) DEFAULT NULL COMMENT 'Determina se o aluno (já) foi aprovado ou não',
  `chaveCidade` int(11) NOT NULL COMMENT 'Identificador da cidade a qual essa matrícula se refere',
  `desconto_individual` float NOT NULL DEFAULT '0' COMMENT 'Desconto individual para o aluno',
  PRIMARY KEY (`idMatricula`),
  KEY `chaveAluno` (`chaveAluno`),
  KEY `chaveCidade` (`chaveCidade`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Matrícula de um aluno em uma etapa em determinado período' AUTO_INCREMENT=36 ;

--
-- Dumping data for table `Matricula`
--

INSERT INTO `Matricula` (`idMatricula`, `chaveAluno`, `etapa`, `aprovado`, `chaveCidade`, `desconto_individual`) VALUES
(1, 11, 1, NULL, 1, 0),
(2, 10, 1, NULL, 1, 0),
(3, 2, 1, NULL, 1, 0),
(4, 1, 1, NULL, 1, 10),
(5, 9, 1, NULL, 1, 0),
(6, 8, 1, NULL, 4, 0),
(7, 7, 1, NULL, 4, 0),
(8, 6, 1, NULL, 4, 0),
(11, 11, 2, NULL, 6, 0),
(12, 6, 1, NULL, 6, 0),
(13, 16, 1, NULL, 6, 0),
(14, 17, 1, NULL, 6, 0),
(15, 18, 1, NULL, 6, 0),
(17, 19, 1, NULL, 6, 0),
(18, 20, 1, NULL, 6, 0),
(19, 21, 1, NULL, 6, 0),
(20, 22, 1, NULL, 6, 0),
(21, 23, 1, NULL, 6, 0),
(22, 24, 1, NULL, 6, 0),
(28, 25, 1, NULL, 6, 0),
(34, 25, 1, NULL, 18, 0),
(35, 26, 1, NULL, 19, 0);

-- --------------------------------------------------------

--
-- Table structure for table `Notificacao`
--

CREATE TABLE IF NOT EXISTS `Notificacao` (
  `idNotificacao` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único dessa notificação',
  `titulo` varchar(100) NOT NULL COMMENT 'Título da notificação a ser dada ao aluno',
  `texto` varchar(500) NOT NULL COMMENT 'Texto da notificação a ser dada ao aluno',
  `chaveAluno` int(11) NOT NULL COMMENT 'Número de matrícula do aluno para o qual deve ser mostrada a notificação',
  `lida` tinyint(1) NOT NULL COMMENT 'Determina se a notificação já foi lida ou não',
  PRIMARY KEY (`idNotificacao`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Representa uma notificação a ser mostrada para o aluno na página principal' AUTO_INCREMENT=108 ;

--
-- Dumping data for table `Notificacao`
--

INSERT INTO `Notificacao` (`idNotificacao`, `titulo`, `texto`, `chaveAluno`, `lida`) VALUES
(1, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$150.00\nData: 13/11/2014\nHorário: 14:27\nMétodo: Dinheiro', 8, 1),
(2, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$70.00\nData: 13/11/2014\nHorário: 16:36\nMétodo: Dinheiro', 7, 0),
(3, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$1.50\nData: 13/11/2014\nHorário: 16:43\nMétodo: Dinheiro', 7, 0),
(4, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$1,000.00\nData: 13/11/2014\nHorário: 16:49\nMétodo: Dinheiro', 7, 0),
(5, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$500.00\nData: 13/11/2014\nHorário: 16:50\nMétodo: Dinheiro', 7, 0),
(6, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$500.00\nData: 13/11/2014\nHorário: 17:05\nMétodo: Dinheiro', 7, 0),
(18, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$100.00\nData: 17/11/2014\nHorário: 09:24\nMétodo: Dinheiro', 11, 1),
(19, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 09:40\nMétodo: Dinheiro', 11, 1),
(20, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 10:00\nMétodo: Dinheiro', 2, 0),
(23, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 10:05\nMétodo: Dinheiro', 11, 1),
(24, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 10:05\nMétodo: Dinheiro', 11, 1),
(26, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 10:14\nMétodo: Dinheiro', 2, 0),
(28, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 10:17\nMétodo: Dinheiro', 2, 0),
(29, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 10:17\nMétodo: Dinheiro', 2, 0),
(30, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 10:18\nMétodo: Dinheiro', 2, 0),
(31, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 10:20\nMétodo: Dinheiro', 2, 0),
(33, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$1.00\nData: 17/11/2014\nHorário: 10:25\nMétodo: Dinheiro', 2, 0),
(34, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 10:27\nMétodo: Dinheiro', 2, 0),
(42, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$1.00\nData: 17/11/2014\nHorário: 10:30\nMétodo: Dinheiro', 2, 0),
(43, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$1.00\nData: 17/11/2014\nHorário: 10:30\nMétodo: Dinheiro', 2, 0),
(44, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$1.00\nData: 17/11/2014\nHorário: 10:31\nMétodo: Dinheiro', 2, 0),
(45, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 10:31\nMétodo: Dinheiro', 2, 0),
(46, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$20.00\nData: 17/11/2014\nHorário: 10:32\nMétodo: Dinheiro', 2, 0),
(47, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$20.00\nData: 17/11/2014\nHorário: 10:33\nMétodo: Dinheiro', 2, 0),
(48, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 10:34\nMétodo: Dinheiro', 2, 0),
(49, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 10:36\nMétodo: Dinheiro', 2, 0),
(51, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$10.00\nData: 17/11/2014\nHorário: 10:38\nMétodo: Dinheiro', 2, 0),
(52, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$20.00\nData: 17/11/2014\nHorário: 10:39\nMétodo: Dinheiro', 2, 0),
(53, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 10:45\nMétodo: Dinheiro', 2, 0),
(54, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$10.00\nData: 17/11/2014\nHorário: 10:47\nMétodo: Dinheiro', 2, 0),
(55, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$1.00\nData: 17/11/2014\nHorário: 10:49\nMétodo: Dinheiro', 2, 0),
(56, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$1.00\nData: 17/11/2014\nHorário: 10:50\nMétodo: Dinheiro', 2, 0),
(57, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$1.00\nData: 17/11/2014\nHorário: 10:50\nMétodo: Dinheiro', 2, 0),
(58, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$1.00\nData: 17/11/2014\nHorário: 10:53\nMétodo: Dinheiro', 2, 0),
(59, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$1.00\nData: 17/11/2014\nHorário: 10:54\nMétodo: Dinheiro', 2, 0),
(60, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$5.00\nData: 17/11/2014\nHorário: 10:55\nMétodo: Dinheiro', 2, 0),
(61, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$5.00\nData: 17/11/2014\nHorário: 10:56\nMétodo: Dinheiro', 2, 0),
(62, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$80.00\nData: 17/11/2014\nHorário: 10:56\nMétodo: Dinheiro', 2, 0),
(63, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 10:57\nMétodo: Dinheiro', 2, 0),
(64, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 10:57\nMétodo: Dinheiro', 2, 0),
(65, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 10:58\nMétodo: Dinheiro', 2, 0),
(66, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$1.00\nData: 17/11/2014\nHorário: 10:59\nMétodo: Dinheiro', 2, 0),
(67, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$89.00\nData: 17/11/2014\nHorário: 10:59\nMétodo: Dinheiro', 2, 0),
(68, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$100.00\nData: 17/11/2014\nHorário: 11:00\nMétodo: Dinheiro', 2, 0),
(69, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 11:01\nMétodo: Dinheiro', 2, 0),
(70, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 11:01\nMétodo: Dinheiro', 2, 0),
(71, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 11:01\nMétodo: Dinheiro', 2, 0),
(72, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 11:02\nMétodo: Dinheiro', 2, 0),
(73, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 11:03\nMétodo: Dinheiro', 2, 0),
(74, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 11:03\nMétodo: Dinheiro', 2, 0),
(75, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 11:08\nMétodo: Dinheiro', 2, 0),
(76, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 17/11/2014\nHorário: 11:10\nMétodo: Dinheiro', 2, 0),
(77, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$100.00\nData: 17/11/2014\nHorário: 11:10\nMétodo: Dinheiro', 2, 0),
(78, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$60.00\nData: 17/11/2014\nHorário: 11:10\nMétodo: Dinheiro', 2, 0),
(79, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$100.00\nData: 17/11/2014\nHorário: 11:13\nMétodo: Dinheiro', 2, 0),
(80, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$100.00\nData: 17/11/2014\nHorário: 11:16\nMétodo: Dinheiro', 2, 0),
(81, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$100.00\nData: 17/11/2014\nHorário: 11:18\nMétodo: Dinheiro', 2, 0),
(82, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$569.00\nData: 17/11/2014\nHorário: 11:50\nMétodo: Dinheiro', 7, 0),
(83, 'Ausência na aula do dia 02/02/2014', 'Uma ausência sua foi registrada para a aula do dia 02/02/2014\nCaso esse dado não esteja correto, favor contatar o coordenador da sua cidade.', 6, 1),
(84, 'Ausência na aula do dia 02/02/2014', 'Uma ausência sua foi registrada para a aula do dia 02/02/2014\nCaso esse dado não esteja correto, favor contatar o coordenador da sua cidade ou registrar uma justificativa no sistema.', 6, 1),
(85, 'Ausência na aula do dia 02/02/2014', 'Uma ausência sua foi registrada para a aula do dia 02/02/2014\nCaso esse dado não esteja correto, favor contatar o coordenador da sua cidade ou registrar uma justificativa no sistema.', 6, 1),
(86, 'Ausência na aula do dia 02/02/2014', 'Uma ausência sua foi registrada para a aula do dia 02/02/2014\nCaso esse dado não esteja correto, favor contatar o coordenador da sua cidade ou registrar uma justificativa no sistema.', 6, 1),
(87, 'Justificativa de ausência negada', 'Sua justificativa de ausência foi negada\nCaso você acredite que houve algum erro de julgamento por parte do avaliador da sua justificativa, favor entrar em contato conosco.', 6, 1),
(88, 'Justificativa de ausência aceita', 'Sua justificativa de ausência foi aceita.', 6, 1),
(89, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 18/11/2014\nHorário: 08:09\nMétodo: Dinheiro', 11, 1),
(91, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 18/11/2014\nHorário: 08:15\nMétodo: Dinheiro', 11, 1),
(92, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 18/11/2014\nHorário: 08:18\nMétodo: Dinheiro', 11, 1),
(93, 'Desconto por indicação', 'Você recebeu 10% de desconto por ter indicado o(a) aluno(a) : Hernando Hércules Ferreira', 9, 1),
(94, 'Desconto por indicação', ' por sua indicação foi removido das próximas parcelas', 9, 1),
(95, 'Desconto por indicação', 'Um de seus indicados retomou o curso, seu desconto de 10% por sua indicação foi adicionado novamente às próximas parcelas', 9, 1),
(96, 'Desconto por indicação', 'Um de seus indicados desistiu do curso, seu desconto de 10% por sua indicação foi removido das próximas parcelas', 9, 1),
(97, 'Desconto por indicação', 'Um de seus indicados retomou o curso, seu desconto de 10% por sua indicação foi adicionado novamente às próximas parcelas', 9, 1),
(98, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$90.00\nData: 18/11/2014\nHorário: 08:42\nMétodo: Dinheiro', 9, 1),
(99, 'Desconto por indicação', 'Um de seus indicados desistiu do curso, seu desconto de 10% por sua indicação foi removido das próximas parcelas', 9, 1),
(100, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$40.00\nData: 18/11/2014\nHorário: 08:48\nMétodo: Dinheiro', 9, 1),
(101, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$30.00\nData: 18/11/2014\nHorário: 08:49\nMétodo: Dinheiro', 9, 1),
(102, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$20.00\nData: 18/11/2014\nHorário: 08:50\nMétodo: Dinheiro', 9, 1),
(103, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$150.00\nData: 31/01/2015\nHorário: 20:02\nMétodo: Dinheiro', 2, 0),
(104, 'Desconto por indicação', 'Por uma correção do sistema, um aluno corrigiu corrigiu seu indicador para aluno correto, seu desconto de 10% foi removido das próximas parcelas', -1, 0),
(105, 'Desconto por indicação', 'Por uma correção do sistema, um aluno corrigiu corrigiu seu indicador para aluno correto, seu desconto de 10% foi removido das próximas parcelas', -1, 0),
(106, 'Desconto por indicação', 'Por uma correção do sistema, um aluno corrigiu corrigiu seu indicador para aluno correto, seu desconto de 10% foi removido das próximas parcelas', -1, 0),
(107, 'Desconto por indicação', 'Por uma correção do sistema, um aluno corrigiu corrigiu seu indicador para aluno correto, seu desconto de 10% foi removido das próximas parcelas', -1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `Pagamento`
--

CREATE TABLE IF NOT EXISTS `Pagamento` (
  `idPagamento` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único desse pagamento',
  `chaveUsuario` int(11) NOT NULL,
  `valor` float NOT NULL COMMENT 'Valor pago nesse pagamento',
  `data` datetime NOT NULL COMMENT 'Data em que o pagamento foi validado no sistema',
  `metodo` varchar(100) NOT NULL COMMENT 'Método de pagamento',
  `objetivo` enum('mensalidade','anuidade','livro','') NOT NULL COMMENT 'Especifica o que esse pagamento está pagando',
  `codigoTransacao` int(11) DEFAULT NULL COMMENT 'Código da transação no Pagseguro, quando houver',
  `ano` int(11) NOT NULL COMMENT 'Ano ao qual esse pagamento se refere',
  PRIMARY KEY (`idPagamento`),
  KEY `chaveUsuario` (`chaveUsuario`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Pagamento genérico no sistema' AUTO_INCREMENT=5 ;

--
-- Dumping data for table `Pagamento`
--

INSERT INTO `Pagamento` (`idPagamento`, `chaveUsuario`, `valor`, `data`, `metodo`, `objetivo`, `codigoTransacao`, `ano`) VALUES
(1, 8, 500, '0000-00-00 00:00:00', 'Dinheiro', 'mensalidade', NULL, 2014),
(2, 9, 150, '0000-00-00 00:00:00', 'Cheque', 'mensalidade', NULL, 2014),
(3, 9, 70, '0000-00-00 00:00:00', 'Dinheiro', 'mensalidade', NULL, 2014),
(4, 3, 150, '2015-01-31 20:02:58', 'Dinheiro', 'mensalidade', NULL, 2014);

-- --------------------------------------------------------

--
-- Table structure for table `Pedido`
--

CREATE TABLE IF NOT EXISTS `Pedido` (
  `chaveProduto` int(11) NOT NULL COMMENT 'Identificador unico do produto sendo comprado',
  `chaveCompra` int(11) NOT NULL COMMENT 'Identificador unico da compra a qual esse pedido pertence',
  `quantidade` int(10) unsigned NOT NULL COMMENT 'Quantidade desse produto que sera comprada',
  PRIMARY KEY (`chaveCompra`,`chaveProduto`),
  KEY `livroComprado` (`chaveProduto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Compra de um produto especifico,referenciado por uma compra de mais produtos';

-- --------------------------------------------------------

--
-- Table structure for table `PgtoAnuidade`
--

CREATE TABLE IF NOT EXISTS `PgtoAnuidade` (
  `idPagAnuidade` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico de pagamento de anuidade',
  `chaveAssoc` int(11) NOT NULL COMMENT 'Identificador do associado ao qual esse pagamento se refere',
  `inscricao` tinyint(1) NOT NULL COMMENT 'Determina se esse pagamento se refere a uma inscricao ou a uma anuidade',
  `valorTotal` float NOT NULL COMMENT 'Valor total a ser pago nessa anuidade/inscrição',
  `valorPago` float NOT NULL COMMENT 'Valor pago pelo associado',
  `metodo` varchar(100) NOT NULL COMMENT 'Método de pagamento utilizado para essa anuidade',
  `data` datetime DEFAULT NULL COMMENT 'Data do pagamento da anuidade',
  `ano` int(11) NOT NULL COMMENT 'Ano ao qual esse pagamento se refere (pode ser diferente do ano especificado na data)',
  `fechado` tinyint(1) NOT NULL COMMENT 'Determina se o pagamento integral já foi feito ou não',
  PRIMARY KEY (`idPagAnuidade`),
  KEY `chaveAssoc` (`chaveAssoc`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Pagamento da anuidade de um associado' AUTO_INCREMENT=3 ;

--
-- Dumping data for table `PgtoAnuidade`
--

INSERT INTO `PgtoAnuidade` (`idPagAnuidade`, `chaveAssoc`, `inscricao`, `valorTotal`, `valorPago`, `metodo`, `data`, `ano`, `fechado`) VALUES
(1, 1, 1, 300, 300, '', '2014-11-18 00:00:00', 2014, 1),
(2, 1, 0, 1500, 0, '', NULL, 2014, 0);

-- --------------------------------------------------------

--
-- Table structure for table `PgtoCompra`
--

CREATE TABLE IF NOT EXISTS `PgtoCompra` (
  `idPagCompra` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico de pagamento de uma compra',
  `cpf` int(11) NOT NULL COMMENT 'CPF do comprador do produto',
  `valor` float NOT NULL COMMENT 'Valor pago na compra',
  `metodo` int(11) NOT NULL COMMENT 'Método de pagamento utilizado para essa compra',
  `chaveCompra` int(11) NOT NULL COMMENT 'Identificador unico da compra feita, ao qual esse pagamento se refere',
  `data` datetime NOT NULL COMMENT 'Data do pagamento',
  PRIMARY KEY (`idPagCompra`),
  KEY `chaveCompra` (`chaveCompra`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Pagamento de algum produto' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `PgtoMensalidade`
--

CREATE TABLE IF NOT EXISTS `PgtoMensalidade` (
  `idPagMensalidade` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador do pagamento de mensalidade',
  `chaveMatricula` int(11) DEFAULT NULL COMMENT 'Numero da matrícula à qual esse pagamento se refere',
  `numParcela` int(11) NOT NULL COMMENT 'Numero da parcela ao qual esse pagamento se refere (deve ser 0 caso esse pagamento seja de inscrição)',
  `valorTotal` float NOT NULL COMMENT 'Valor total a ser pago nessa mensalidade',
  `valorPago` float NOT NULL COMMENT 'Valor pago pelo aluno',
  `desconto` float NOT NULL COMMENT 'Desconto (em %) recebido pelo aluno devido às indicações',
  `metodo` varchar(100) NOT NULL COMMENT 'Método de pagamento utilizado para essa mensalidade',
  `data` datetime DEFAULT NULL COMMENT 'Data na qual essa mensalidade foi paga',
  `ano` int(11) NOT NULL COMMENT 'Ano ao qual esse pagamento se refere (pode ser diferente do ano especificado na data)',
  `fechado` tinyint(1) NOT NULL COMMENT 'Determina se o pagamento integral já foi feito ou não',
  PRIMARY KEY (`idPagMensalidade`),
  KEY `chaveAluno` (`chaveMatricula`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Pagamento de mensalidade ou inscricao de aluno' AUTO_INCREMENT=301 ;

--
-- Dumping data for table `PgtoMensalidade`
--

INSERT INTO `PgtoMensalidade` (`idPagMensalidade`, `chaveMatricula`, `numParcela`, `valorTotal`, `valorPago`, `desconto`, `metodo`, `data`, `ano`, `fechado`) VALUES
(1, 1, 0, 100, 90, 10, 'Dinheiro', '2014-11-18 00:00:00', 2014, 1),
(2, 1, 1, 30, 0, 10, '', NULL, 2014, 0),
(3, 1, 2, 30, 0, 10, '', NULL, 2014, 0),
(4, 1, 3, 30, 0, 10, '', NULL, 2014, 0),
(5, 1, 4, 30, 0, 10, '', NULL, 2014, 0),
(6, 1, 5, 30, 0, 10, '', NULL, 2014, 0),
(7, 1, 6, 30, 0, 10, '', NULL, 2014, 0),
(8, 1, 7, 30, 0, 10, '', NULL, 2014, 0),
(9, 1, 8, 30, 0, 10, '', NULL, 2014, 0),
(10, 1, 9, 30, 0, 10, '', NULL, 2014, 0),
(11, 1, 10, 30, 0, 10, '', NULL, 2014, 0),
(12, 1, 11, 30, 0, 10, '', NULL, 2014, 0),
(13, 2, 0, 100, 0, 0, '', NULL, 2014, 0),
(14, 2, 1, 30, 0, 0, '', NULL, 2014, 0),
(15, 2, 2, 30, 0, 0, '', NULL, 2014, 0),
(16, 2, 3, 30, 0, 0, '', NULL, 2014, 0),
(17, 2, 4, 30, 0, 0, '', NULL, 2014, 0),
(18, 2, 5, 30, 0, 0, '', NULL, 2014, 0),
(19, 2, 6, 30, 0, 0, '', NULL, 2014, 0),
(20, 2, 7, 30, 0, 0, '', NULL, 2014, 0),
(21, 2, 8, 30, 0, 0, '', NULL, 2014, 0),
(22, 2, 9, 30, 0, 0, '', NULL, 2014, 0),
(23, 2, 10, 30, 0, 0, '', NULL, 2014, 0),
(24, 2, 11, 30, 0, 0, '', NULL, 2014, 0),
(25, 3, 0, 100, 90, 10, 'Dinheiro', '2015-01-31 00:00:00', 2014, 1),
(26, 3, 1, 30, 27, 10, 'Dinheiro', '2015-01-31 00:00:00', 2014, 1),
(27, 3, 2, 30, 27, 10, 'Dinheiro', '2015-01-31 00:00:00', 2014, 1),
(28, 3, 3, 30, 6, 10, 'Dinheiro', '2015-01-31 00:00:00', 2014, 0),
(29, 3, 4, 30, 0, 10, '', NULL, 2014, 0),
(30, 3, 5, 30, 0, 10, '', NULL, 2014, 0),
(31, 3, 6, 30, 0, 10, '', NULL, 2014, 0),
(32, 3, 7, 30, 0, 10, '', NULL, 2014, 0),
(33, 3, 8, 30, 0, 10, '', NULL, 2014, 0),
(34, 3, 9, 30, 0, 10, '', NULL, 2014, 0),
(35, 3, 10, 30, 0, 10, '', NULL, 2014, 0),
(36, 3, 11, 30, 0, 10, '', NULL, 2014, 0),
(37, 4, 0, 100, 0, 10, '', NULL, 2014, 0),
(38, 4, 1, 30, 0, 10, '', NULL, 2014, 0),
(39, 4, 2, 30, 0, 10, '', NULL, 2014, 0),
(40, 4, 3, 30, 0, 10, '', NULL, 2014, 0),
(41, 4, 4, 30, 0, 10, '', NULL, 2014, 0),
(42, 4, 5, 30, 0, 10, '', NULL, 2014, 0),
(43, 4, 6, 30, 0, 10, '', NULL, 2014, 0),
(44, 4, 7, 30, 0, 10, '', NULL, 2014, 0),
(45, 4, 8, 30, 0, 10, '', NULL, 2014, 0),
(46, 4, 9, 30, 0, 10, '', NULL, 2014, 0),
(47, 4, 10, 30, 0, 10, '', NULL, 2014, 0),
(48, 4, 11, 30, 0, 10, '', NULL, 2014, 0),
(49, 5, 0, 100, 90, 10, 'Dinheiro', '2014-11-18 00:00:00', 2014, 1),
(50, 5, 1, 30, 30, 0, 'Dinheiro', '2014-11-18 00:00:00', 2014, 1),
(51, 5, 2, 30, 30, 0, 'Dinheiro', '2014-11-18 00:00:00', 2014, 1),
(52, 5, 3, 30, 30, 0, 'Dinheiro', '2014-11-18 00:00:00', 2014, 1),
(53, 5, 4, 30, 0, 0, '', NULL, 2014, 0),
(54, 5, 5, 30, 0, 0, '', NULL, 2014, 0),
(55, 5, 6, 30, 0, 0, '', NULL, 2014, 0),
(56, 5, 7, 30, 0, 0, '', NULL, 2014, 0),
(57, 5, 8, 30, 0, 0, '', NULL, 2014, 0),
(58, 5, 9, 30, 0, 0, '', NULL, 2014, 0),
(59, 5, 10, 30, 0, 0, '', NULL, 2014, 0),
(60, 5, 11, 30, 0, 0, '', NULL, 2014, 0),
(61, 6, 0, 150, 0, 0, '', NULL, 2014, 0),
(62, 6, 1, 220, 0, 0, '', NULL, 2014, 0),
(63, 6, 2, 220, 0, 0, '', NULL, 2014, 0),
(64, 6, 3, 220, 0, 0, '', NULL, 2014, 0),
(65, 6, 4, 220, 0, 0, '', NULL, 2014, 0),
(66, 6, 5, 220, 0, 0, '', NULL, 2014, 0),
(67, 6, 6, 220, 0, 0, '', NULL, 2014, 0),
(68, 6, 7, 220, 0, 0, '', NULL, 2014, 0),
(69, 6, 8, 220, 0, 0, '', NULL, 2014, 0),
(70, 6, 9, 220, 0, 0, '', NULL, 2014, 0),
(71, 6, 10, 220, 0, 0, '', NULL, 2014, 0),
(72, 6, 11, 220, 0, 0, '', NULL, 2014, 0),
(73, 7, 0, 150, 0, 0, '', NULL, 2014, 0),
(74, 7, 1, 220, 0, 0, '', NULL, 2014, 0),
(75, 7, 2, 220, 0, 0, '', NULL, 2014, 0),
(76, 7, 3, 220, 0, 0, '', NULL, 2014, 0),
(77, 7, 4, 220, 0, 0, '', NULL, 2014, 0),
(78, 7, 5, 220, 0, 0, '', NULL, 2014, 0),
(79, 7, 6, 220, 0, 0, '', NULL, 2014, 0),
(80, 7, 7, 220, 0, 0, '', NULL, 2014, 0),
(81, 7, 8, 220, 0, 0, '', NULL, 2014, 0),
(82, 7, 9, 220, 0, 0, '', NULL, 2014, 0),
(83, 7, 10, 220, 0, 0, '', NULL, 2014, 0),
(84, 7, 11, 220, 0, 0, '', NULL, 2014, 0),
(85, 8, 0, 150, 0, 0, '', NULL, 2014, 0),
(86, 8, 1, 220, 0, 0, '', NULL, 2014, 0),
(87, 8, 2, 220, 0, 0, '', NULL, 2014, 0),
(88, 8, 3, 220, 0, 0, '', NULL, 2014, 0),
(89, 8, 4, 220, 0, 0, '', NULL, 2014, 0),
(90, 8, 5, 220, 0, 0, '', NULL, 2014, 0),
(91, 8, 6, 220, 0, 0, '', NULL, 2014, 0),
(92, 8, 7, 220, 0, 0, '', NULL, 2014, 0),
(93, 8, 8, 220, 0, 0, '', NULL, 2014, 0),
(94, 8, 9, 220, 0, 0, '', NULL, 2014, 0),
(95, 8, 10, 220, 0, 0, '', NULL, 2014, 0),
(96, 8, 11, 220, 0, 0, '', NULL, 2014, 0),
(97, NULL, 0, 100, 0, 0, '', NULL, 2015, 0),
(98, NULL, 1, 80, 0, 0, '', NULL, 2015, 0),
(99, NULL, 2, 80, 0, 0, '', NULL, 2015, 0),
(100, NULL, 3, 80, 0, 0, '', NULL, 2015, 0),
(101, NULL, 4, 80, 0, 0, '', NULL, 2015, 0),
(102, NULL, 5, 80, 0, 0, '', NULL, 2015, 0),
(103, NULL, 6, 80, 0, 0, '', NULL, 2015, 0),
(104, NULL, 7, 80, 0, 0, '', NULL, 2015, 0),
(105, NULL, 8, 80, 0, 0, '', NULL, 2015, 0),
(106, NULL, 9, 80, 0, 0, '', NULL, 2015, 0),
(107, NULL, 10, 80, 0, 0, '', NULL, 2015, 0),
(108, NULL, 11, 80, 0, 0, '', NULL, 2015, 0),
(109, NULL, 0, 100, 0, 0, '', NULL, 2015, 0),
(110, NULL, 1, 80, 0, 0, '', NULL, 2015, 0),
(111, NULL, 2, 80, 0, 0, '', NULL, 2015, 0),
(112, NULL, 3, 80, 0, 0, '', NULL, 2015, 0),
(113, NULL, 4, 80, 0, 0, '', NULL, 2015, 0),
(114, NULL, 5, 80, 0, 0, '', NULL, 2015, 0),
(115, NULL, 6, 80, 0, 0, '', NULL, 2015, 0),
(116, NULL, 7, 80, 0, 0, '', NULL, 2015, 0),
(117, NULL, 8, 80, 0, 0, '', NULL, 2015, 0),
(118, NULL, 9, 80, 0, 0, '', NULL, 2015, 0),
(119, NULL, 10, 80, 0, 0, '', NULL, 2015, 0),
(120, NULL, 11, 80, 0, 0, '', NULL, 2015, 0),
(121, 11, 0, 100, 0, 0, '', NULL, 2015, 0),
(122, 11, 1, 80, 0, 0, '', NULL, 2015, 0),
(123, 11, 2, 80, 0, 0, '', NULL, 2015, 0),
(124, 11, 3, 80, 0, 0, '', NULL, 2015, 0),
(125, 11, 4, 80, 0, 0, '', NULL, 2015, 0),
(126, 11, 5, 80, 0, 0, '', NULL, 2015, 0),
(127, 11, 6, 80, 0, 0, '', NULL, 2015, 0),
(128, 11, 7, 80, 0, 0, '', NULL, 2015, 0),
(129, 11, 8, 80, 0, 0, '', NULL, 2015, 0),
(130, 11, 9, 80, 0, 0, '', NULL, 2015, 0),
(131, 11, 10, 80, 0, 0, '', NULL, 2015, 0),
(132, 11, 11, 80, 0, 0, '', NULL, 2015, 0),
(133, 12, 0, 100, 0, 0, '', NULL, 2015, 0),
(134, 12, 1, 80, 0, 0, '', NULL, 2015, 0),
(135, 12, 2, 80, 0, 0, '', NULL, 2015, 0),
(136, 12, 3, 80, 0, 0, '', NULL, 2015, 0),
(137, 12, 4, 80, 0, 0, '', NULL, 2015, 0),
(138, 12, 5, 80, 0, 0, '', NULL, 2015, 0),
(139, 12, 6, 80, 0, 0, '', NULL, 2015, 0),
(140, 12, 7, 80, 0, 0, '', NULL, 2015, 0),
(141, 12, 8, 80, 0, 0, '', NULL, 2015, 0),
(142, 12, 9, 80, 0, 0, '', NULL, 2015, 0),
(143, 12, 10, 80, 0, 0, '', NULL, 2015, 0),
(144, 12, 11, 80, 0, 0, '', NULL, 2015, 0),
(145, 13, 0, 100, 0, 0, '', NULL, 2015, 0),
(146, 13, 1, 80, 0, 0, '', NULL, 2015, 0),
(147, 13, 2, 80, 0, 0, '', NULL, 2015, 0),
(148, 13, 3, 80, 0, 0, '', NULL, 2015, 0),
(149, 13, 4, 80, 0, 0, '', NULL, 2015, 0),
(150, 13, 5, 80, 0, 0, '', NULL, 2015, 0),
(151, 13, 6, 80, 0, 0, '', NULL, 2015, 0),
(152, 13, 7, 80, 0, 0, '', NULL, 2015, 0),
(153, 13, 8, 80, 0, 0, '', NULL, 2015, 0),
(154, 13, 9, 80, 0, 0, '', NULL, 2015, 0),
(155, 13, 10, 80, 0, 0, '', NULL, 2015, 0),
(156, 13, 11, 80, 0, 0, '', NULL, 2015, 0),
(157, 14, 0, 100, 0, 0, '', NULL, 2015, 0),
(158, 14, 1, 80, 0, 0, '', NULL, 2015, 0),
(159, 14, 2, 80, 0, 0, '', NULL, 2015, 0),
(160, 14, 3, 80, 0, 0, '', NULL, 2015, 0),
(161, 14, 4, 80, 0, 0, '', NULL, 2015, 0),
(162, 14, 5, 80, 0, 0, '', NULL, 2015, 0),
(163, 14, 6, 80, 0, 0, '', NULL, 2015, 0),
(164, 14, 7, 80, 0, 0, '', NULL, 2015, 0),
(165, 14, 8, 80, 0, 0, '', NULL, 2015, 0),
(166, 14, 9, 80, 0, 0, '', NULL, 2015, 0),
(167, 14, 10, 80, 0, 0, '', NULL, 2015, 0),
(168, 14, 11, 80, 0, 0, '', NULL, 2015, 0),
(169, 15, 0, 0, 0, 0, '', NULL, 2015, 0),
(170, 15, 1, 0, 0, 0, '', NULL, 2015, 0),
(171, 15, 2, 0, 0, 0, '', NULL, 2015, 0),
(172, 15, 3, 0, 0, 0, '', NULL, 2015, 0),
(173, 15, 4, 0, 0, 0, '', NULL, 2015, 0),
(174, 15, 5, 0, 0, 0, '', NULL, 2015, 0),
(175, 15, 6, 0, 0, 0, '', NULL, 2015, 0),
(176, 15, 7, 0, 0, 0, '', NULL, 2015, 0),
(177, 15, 8, 0, 0, 0, '', NULL, 2015, 0),
(178, 15, 9, 0, 0, 0, '', NULL, 2015, 0),
(179, 15, 10, 0, 0, 0, '', NULL, 2015, 0),
(180, 15, 11, 0, 0, 0, '', NULL, 2015, 0),
(181, 16, 0, 0, 0, 0, '', NULL, 2015, 0),
(182, 16, 1, 0, 0, 0, '', NULL, 2015, 0),
(183, 16, 2, 0, 0, 0, '', NULL, 2015, 0),
(184, 16, 3, 0, 0, 0, '', NULL, 2015, 0),
(185, 16, 4, 0, 0, 0, '', NULL, 2015, 0),
(186, 16, 5, 0, 0, 0, '', NULL, 2015, 0),
(187, 16, 6, 0, 0, 0, '', NULL, 2015, 0),
(188, 16, 7, 0, 0, 0, '', NULL, 2015, 0),
(189, 16, 8, 0, 0, 0, '', NULL, 2015, 0),
(190, 16, 9, 0, 0, 0, '', NULL, 2015, 0),
(191, 16, 10, 0, 0, 0, '', NULL, 2015, 0),
(192, 16, 11, 0, 0, 0, '', NULL, 2015, 0),
(193, 17, 0, 100, 0, 0, '', NULL, 2015, 0),
(194, 17, 1, 80, 0, 0, '', NULL, 2015, 0),
(195, 17, 2, 80, 0, 0, '', NULL, 2015, 0),
(196, 17, 3, 80, 0, 0, '', NULL, 2015, 0),
(197, 17, 4, 80, 0, 0, '', NULL, 2015, 0),
(198, 17, 5, 80, 0, 0, '', NULL, 2015, 0),
(199, 17, 6, 80, 0, 0, '', NULL, 2015, 0),
(200, 17, 7, 80, 0, 0, '', NULL, 2015, 0),
(201, 17, 8, 80, 0, 0, '', NULL, 2015, 0),
(202, 17, 9, 80, 0, 0, '', NULL, 2015, 0),
(203, 17, 10, 80, 0, 0, '', NULL, 2015, 0),
(204, 17, 11, 80, 0, 0, '', NULL, 2015, 0),
(205, 18, 0, 0, 0, 0, '', NULL, 2015, 0),
(206, 18, 1, 0, 0, 0, '', NULL, 2015, 0),
(207, 18, 2, 0, 0, 0, '', NULL, 2015, 0),
(208, 18, 3, 0, 0, 0, '', NULL, 2015, 0),
(209, 18, 4, 0, 0, 0, '', NULL, 2015, 0),
(210, 18, 5, 0, 0, 0, '', NULL, 2015, 0),
(211, 18, 6, 0, 0, 0, '', NULL, 2015, 0),
(212, 18, 7, 0, 0, 0, '', NULL, 2015, 0),
(213, 18, 8, 0, 0, 0, '', NULL, 2015, 0),
(214, 18, 9, 0, 0, 0, '', NULL, 2015, 0),
(215, 18, 10, 0, 0, 0, '', NULL, 2015, 0),
(216, 18, 11, 0, 0, 0, '', NULL, 2015, 0),
(217, 19, 0, 0, 0, 0, '', NULL, 2015, 0),
(218, 19, 1, 0, 0, 0, '', NULL, 2015, 0),
(219, 19, 2, 0, 0, 0, '', NULL, 2015, 0),
(220, 19, 3, 0, 0, 0, '', NULL, 2015, 0),
(221, 19, 4, 0, 0, 0, '', NULL, 2015, 0),
(222, 19, 5, 0, 0, 0, '', NULL, 2015, 0),
(223, 19, 6, 0, 0, 0, '', NULL, 2015, 0),
(224, 19, 7, 0, 0, 0, '', NULL, 2015, 0),
(225, 19, 8, 0, 0, 0, '', NULL, 2015, 0),
(226, 19, 9, 0, 0, 0, '', NULL, 2015, 0),
(227, 19, 10, 0, 0, 0, '', NULL, 2015, 0),
(228, 19, 11, 0, 0, 0, '', NULL, 2015, 0),
(229, 20, 0, 150, 0, 0, '', NULL, 2015, 0),
(230, 20, 1, 0, 0, 0, '', NULL, 2015, 0),
(231, 20, 2, 0, 0, 0, '', NULL, 2015, 0),
(232, 20, 3, 0, 0, 0, '', NULL, 2015, 0),
(233, 20, 4, 0, 0, 0, '', NULL, 2015, 0),
(234, 20, 5, 0, 0, 0, '', NULL, 2015, 0),
(235, 20, 6, 0, 0, 0, '', NULL, 2015, 0),
(236, 20, 7, 0, 0, 0, '', NULL, 2015, 0),
(237, 20, 8, 0, 0, 0, '', NULL, 2015, 0),
(238, 20, 9, 0, 0, 0, '', NULL, 2015, 0),
(239, 20, 10, 0, 0, 0, '', NULL, 2015, 0),
(240, 20, 11, 0, 0, 0, '', NULL, 2015, 0),
(241, 21, 0, 150, 0, 0, '', NULL, 2015, 0),
(242, 21, 1, 200, 0, 0, '', NULL, 2015, 0),
(243, 21, 2, 200, 0, 0, '', NULL, 2015, 0),
(244, 21, 3, 200, 0, 0, '', NULL, 2015, 0),
(245, 21, 4, 200, 0, 0, '', NULL, 2015, 0),
(246, 21, 5, 200, 0, 0, '', NULL, 2015, 0),
(247, 21, 6, 200, 0, 0, '', NULL, 2015, 0),
(248, 21, 7, 200, 0, 0, '', NULL, 2015, 0),
(249, 21, 8, 200, 0, 0, '', NULL, 2015, 0),
(250, 21, 9, 200, 0, 0, '', NULL, 2015, 0),
(251, 21, 10, 200, 0, 0, '', NULL, 2015, 0),
(252, 21, 11, 200, 0, 0, '', NULL, 2015, 0),
(253, 22, 0, 0, 0, 0, '', NULL, 2015, 0),
(254, 22, 1, 0, 0, 0, '', NULL, 2015, 0),
(255, 22, 2, 0, 0, 0, '', NULL, 2015, 0),
(256, 22, 3, 0, 0, 0, '', NULL, 2015, 0),
(257, 22, 4, 0, 0, 0, '', NULL, 2015, 0),
(258, 22, 5, 0, 0, 0, '', NULL, 2015, 0),
(259, 22, 6, 0, 0, 0, '', NULL, 2015, 0),
(260, 22, 7, 0, 0, 0, '', NULL, 2015, 0),
(261, 22, 8, 0, 0, 0, '', NULL, 2015, 0),
(262, 22, 9, 0, 0, 0, '', NULL, 2015, 0),
(263, 22, 10, 0, 0, 0, '', NULL, 2015, 0),
(264, 22, 11, 0, 0, 0, '', NULL, 2015, 0),
(265, 28, 0, 120, 0, 0, '', NULL, 2015, 0),
(266, 28, 1, 120, 0, 0, '', NULL, 2015, 0),
(267, 28, 2, 120, 0, 0, '', NULL, 2015, 0),
(268, 28, 3, 120, 0, 0, '', NULL, 2015, 0),
(269, 28, 4, 120, 0, 0, '', NULL, 2015, 0),
(270, 28, 5, 120, 0, 0, '', NULL, 2015, 0),
(271, 28, 6, 120, 0, 0, '', NULL, 2015, 0),
(272, 28, 7, 120, 0, 0, '', NULL, 2015, 0),
(273, 28, 8, 120, 0, 0, '', NULL, 2015, 0),
(274, 28, 9, 120, 0, 0, '', NULL, 2015, 0),
(275, 28, 10, 120, 0, 0, '', NULL, 2015, 0),
(276, 28, 11, 120, 0, 0, '', NULL, 2015, 0),
(277, 34, 0, 250, 0, 0, '', NULL, 2015, 0),
(278, 34, 1, 100, 0, 0, '', NULL, 2015, 0),
(279, 34, 2, 100, 0, 0, '', NULL, 2015, 0),
(280, 34, 3, 100, 0, 0, '', NULL, 2015, 0),
(281, 34, 4, 100, 0, 0, '', NULL, 2015, 0),
(282, 34, 5, 100, 0, 0, '', NULL, 2015, 0),
(283, 34, 6, 100, 0, 0, '', NULL, 2015, 0),
(284, 34, 7, 100, 0, 0, '', NULL, 2015, 0),
(285, 34, 8, 100, 0, 0, '', NULL, 2015, 0),
(286, 34, 9, 100, 0, 0, '', NULL, 2015, 0),
(287, 34, 10, 100, 0, 0, '', NULL, 2015, 0),
(288, 34, 11, 100, 0, 0, '', NULL, 2015, 0),
(289, 35, 0, 2000, 0, 0, '', NULL, 2015, 0),
(290, 35, 1, 100, 0, 0, '', NULL, 2015, 0),
(291, 35, 2, 100, 0, 0, '', NULL, 2015, 0),
(292, 35, 3, 100, 0, 0, '', NULL, 2015, 0),
(293, 35, 4, 100, 0, 0, '', NULL, 2015, 0),
(294, 35, 5, 100, 0, 0, '', NULL, 2015, 0),
(295, 35, 6, 100, 0, 0, '', NULL, 2015, 0),
(296, 35, 7, 100, 0, 0, '', NULL, 2015, 0),
(297, 35, 8, 100, 0, 0, '', NULL, 2015, 0),
(298, 35, 9, 100, 0, 0, '', NULL, 2015, 0),
(299, 35, 10, 100, 0, 0, '', NULL, 2015, 0),
(300, 35, 11, 100, 0, 0, '', NULL, 2015, 0);

-- --------------------------------------------------------

--
-- Table structure for table `Reuniao`
--

CREATE TABLE IF NOT EXISTS `Reuniao` (
  `idReuniao` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico',
  `tema` varchar(200) NOT NULL,
  `data` datetime NOT NULL COMMENT 'Data em que ocorrera a reuniao',
  `descricao` varchar(3000) NOT NULL,
  `local` varchar(500) NOT NULL,
  PRIMARY KEY (`idReuniao`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Dados de reunião a serem mostrados no site' AUTO_INCREMENT=2 ;

--
-- Dumping data for table `Reuniao`
--

INSERT INTO `Reuniao` (`idReuniao`, `tema`, `data`, `descricao`, `local`) VALUES
(1, 'Avaliação dos associados', '2014-11-15 15:30:00', 'Encontro dos associados [...]', 'Praça do Papa');

-- --------------------------------------------------------

--
-- Table structure for table `Trabalho`
--

CREATE TABLE IF NOT EXISTS `Trabalho` (
  `idTrabalho` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico do trabalho',
  `chaveAluno` int(11) NOT NULL COMMENT 'Numero de inscricao do aluno que fez esse trabalho',
  `dataEntrega` datetime NOT NULL COMMENT 'Data e hora em que esse trabalho foi entregue',
  `chaveDefinicao` int(11) NOT NULL COMMENT 'Identificador unico da especificacao do trabalho enviado',
  `nota` int(10) unsigned DEFAULT NULL COMMENT 'Nota do trabalho',
  `comentarioProfessor` varchar(5000) DEFAULT NULL COMMENT 'Comentário do professor sobre o trabalho do aluno',
  `extensao` char(10) NOT NULL COMMENT 'Tipo de arquivo enviado (pdf, doc, ppt, etc)',
  PRIMARY KEY (`idTrabalho`),
  KEY `chaveAluno` (`chaveAluno`),
  KEY `chaveDefinicao` (`chaveDefinicao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Trabalho enviado por aluno' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `TrabalhoDefinicao`
--

CREATE TABLE IF NOT EXISTS `TrabalhoDefinicao` (
  `idDefTrabalho` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico de definicao de trabalho',
  `titulo` varchar(300) NOT NULL COMMENT 'Titulo do trabalho',
  `etapa` int(10) unsigned NOT NULL COMMENT 'Etapa do curso a qual se refere esse trabalho',
  `descricao` varchar(10000) NOT NULL,
  `dataLimite` datetime NOT NULL COMMENT 'Data e hora limite de entrega do trabalho',
  `ano` int(11) NOT NULL COMMENT 'Ano ao qual esse trabalho se refere',
  PRIMARY KEY (`idDefTrabalho`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Especificacao dada para a confeccao de um trabalho por parte dos alunos' AUTO_INCREMENT=5 ;

--
-- Dumping data for table `TrabalhoDefinicao`
--

INSERT INTO `TrabalhoDefinicao` (`idDefTrabalho`, `titulo`, `etapa`, `descricao`, `dataLimite`, `ano`) VALUES
(1, 'Introdução à Homeopatia', 1, 'Trabalho introdutório para as turmas:\r\n\r\nVocê deve fazer um trabalho que [...]', '2014-06-09 00:00:00', 2014),
(2, 'Revisão do curso', 4, 'Esse trabalho deve resumir tudo o que você já aprendeu.\r\n\r\nVocê deve [...]', '2014-12-10 00:00:00', 2014),
(3, 'Problemas de homeopatia', 3, 'Você deve listar todos os problemas no cenário da Homeopatia atual, fazendo [...]', '2015-09-09 00:00:00', 2014),
(4, 'Trabalho orientado', 4, 'Com a ajuda do seu orientador, [...]', '2014-06-10 00:00:00', 2014);

-- --------------------------------------------------------

--
-- Table structure for table `Usuario`
--

CREATE TABLE IF NOT EXISTS `Usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único de usuário',
  `cpf` char(11) NOT NULL COMMENT 'Cadastro de Pessoa Fisica do usuario',
  `dataInscricao` datetime NOT NULL COMMENT 'Data de inscricao no sistema',
  `email` varchar(100) NOT NULL,
  `login` varchar(100) NOT NULL,
  `senha` text NOT NULL,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  KEY `cpf` (`cpf`),
  KEY `cpf_2` (`cpf`),
  KEY `dataInscricao` (`dataInscricao`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Usuario do sistema, que pode ser aluno, associado ou administrador' AUTO_INCREMENT=43 ;

--
-- Dumping data for table `Usuario`
--

INSERT INTO `Usuario` (`id`, `cpf`, `dataInscricao`, `email`, `login`, `senha`, `nome`) VALUES
(1, '11989183654', '2014-07-14 11:31:56', 'luc.aug.freire@gmail.com', 'admin', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'Lucas'),
(2, '81763492168', '2014-11-12 10:12:49', 'victorcastrocarvalho@armyspy.com', 'victor1994', '$2a$08$z/UiLE9vtnQvH.6O0TfJV.YRE9ORaQkwp5NDicR4Om5UQn5rJQgaC', 'Victor Castro Carvalho'),
(3, '64705249070', '2014-11-12 10:15:41', 'ViniciusAlvesSilva@teleworm.us', 'vinicius', '$2a$08$hJj9/hBGZfnrblpx.muqC.zlhru0j./kvn/9Hdz2HTQlsGUHlmJ26', 'Vinicius Alves Silva'),
(4, '83834893315', '2014-11-12 10:21:24', 'MarianaFerreiraPinto@armyspy.com', 'mariana1234', '$2a$08$OsTyrZYsCSvMd4evTtFYA.O3UTY95oL05Y4t6M0JHYvlmbJ9m9NVC', 'Mariana Ferreira Pinto'),
(5, '44236727315', '2014-11-12 10:24:16', 'annalimabarbosa@dayrep.com', 'annalima', '$2a$08$bwSrAdafNIsPRIkONKjWR.eZhRUioqwuGzHDFjM4jwxrIUKVtTtyi', 'Anna Lima Barbosa'),
(6, '37128813128', '2014-11-12 10:29:07', 'feliperodriguesaraujo@rhyta.com', 'felipearaujo', '$2a$08$7iem7gWUy3uHYSXBrPoT5u3kEFIKEEqgBphK9fVeNLYGMZSVxRzb6', 'Felipe Rodrigues Araujo'),
(7, '39098656749', '2014-11-12 10:33:22', 'miguelsilvamartins@rhyta.com', 'miguelaluno', '$2a$08$otpV.e3xH0R6Sr1R3KOncOBZuNEoq24BvVVda.j07vQOp362WPaBK', 'Miguel Silva Martins'),
(8, '51841633011', '2014-11-12 10:36:05', 'antonio92@gmail.com', 'antonsil', '$2a$08$.BdNbxh8/A4Dljgh0ArZ6e3gtl0YtC5H8BTRGco09S/Ug9Z2V711q', 'Antônio José Silva'),
(9, '66242073455', '2014-11-12 10:40:10', 'amanda_ana@gmail.com', 'amanda', '$2a$08$AGDpYen1A3lD1KfMf5DZwubsXYZErmwnswGEApKL2eZaI/68ZGghq', 'Amanda Joana Pereira'),
(10, '02035862981', '2014-11-12 10:42:33', 'getulio_covers@gmail.com', 'getulio', '$2a$08$tcw/wvRLdYh/BJa7wFR1SO4vGwcat56OpHBYnSSCJJBIAiRkreQ7O', 'Getúlio Soares Albuquerque'),
(11, '23146265168', '2014-11-12 10:59:35', 'alfredo22@gmail.com', 'wellin', '$2a$08$rFHwv7LDO6tAN/HVEsqoS.JlBLceSNewBidtjaeMNeiX3kz1iUDUq', 'Wellington Alfredo Dias'),
(12, '61055784748', '2014-11-12 11:00:44', 'herc@gmail.com', 'hercules', '$2a$08$UIKre.q/kHjOQrTAXQjB3.yYVVFW9l.y6BxiUlVbDyxzsk6p//5qK', 'Hernando Hércules Ferreira'),
(13, '85479936492', '2014-11-12 11:06:36', 'joaojoao@gmail.com', 'joaocarlos', '$2a$08$4L7KmSz2RitcwdW9lkp48uI7uZM3a525FF7qotDhzVY7LEn4651rG', 'João Carlos Alberto'),
(14, '77721252245', '2014-11-12 11:08:20', 'ame.joana@gmail.com', 'amelia_flor', '$2a$08$HcZfTXY/8Kdylekb/9dSYO9HE8JIn8pu31bQKDB7DhfQ8n6YXF5Sa', 'Amélia Joana Glória'),
(15, '', '2014-11-12 11:10:44', '', '', '$2a$08$aCColQUnDPf.jOP93CKueuSeoSqDSIA82uI5dcqqUoaBI1gAtLgam', ''),
(16, '17125261540', '2014-11-12 11:12:12', 'fernando.filho@gmail.com', 'nando', '$2a$08$axsrInHv6.bBo8J0Ef7qxOAASLAoH4FM2kVnXDmDZApZ5aV1Aes72', 'Fernando Faria Filho'),
(17, '68322372787', '2014-11-12 11:13:36', 'cassio.murilo@gmail.com', 'camum', '$2a$08$JjcHZKPEMGXqcm2m4R5dsexqGE56BEFI0nj4oyMk6zvWFFTPdkncW', 'Cássio Murilo de Oliveira'),
(18, '77702570776', '2014-11-12 11:14:14', 'jess1231@gmail.com', 'jessica', '$2a$08$dGC9vIFIC4ayGzwx3v16j.Gm9f.cYSfaf9Y.KqiEcA17J1lMvYj86', 'Jéssica Martins Pereira'),
(19, '22783139758', '2014-11-12 11:15:14', 'kaioherb@gmail.com', 'kaio_h', '$2a$08$Re5inJAd5RUtsnwDVhRT.uR4uTeric.L2MdPPVpnz7jFmzYCgxnhS', 'Kaio Herberto Lobo'),
(20, '83872687808', '2014-11-12 11:17:29', 'xavier_x@gmail.com', 'xavier', '$2a$08$LmF6XVIzbiMkWblg9lngdesJqdMq8g33Jyrlgh9MXznFwyTtU856y', 'Xavier Souza Ferreira'),
(21, '64391809834', '2014-11-12 11:18:34', 'monica@gmail.com', 'monica', '$2a$08$sKIsk.1PtkNISMKhi1iw/eLVuXFuE6Omp8mz8/6x1G6953vSh/O86', 'Mônica Horta Freire'),
(22, '74117042963', '2014-11-13 12:59:00', 'lusilveira@gmail.com', 'luiz_homeopat', '$2a$08$pxGqvRKKrHhrJx5ZLTPGaev7PeZw/zSNTBOeCDMs8irFfEroMTGXO', 'Luíz Silveira Santana'),
(23, '98818182650', '2014-11-13 12:59:39', 'lsilva@gmail.com', 'lsilva', '$2a$08$5QVYK89G3mMsVom.Viz7zOWZQ8JGDQc/HQLCClQCuOd4xfIWWsRBG', 'Luana Silva Nogueira'),
(24, '32759893910', '2014-11-13 13:00:42', 'sandra_mp@gmail.com', 'sandramp', '$2a$08$5b6vrhQoXT1ak4.pkzGA/uCbDXtqqD.TXRaOQBY35pYP05s1jfiMu', 'Sandra Maria Passos'),
(25, '84272313428', '2014-11-13 13:14:03', 'ednaldo@whatisthebrother.com', 'ednaldop', '$2a$08$7Biq7AD37sJdNBrxXM7CKerTwRbxku8uiVaubQZcXbOSfQ7.aZruq', 'Ednaldo Pereira'),
(26, '81424367794', '2014-11-13 14:07:47', 'luiza.conceicao@gmail.com', 'luconceicao', '$2a$08$Eb4yjC5mPtgpLQ5cO.jJQ.UmnpMKXXkyj1i/MC1RtI6CGyK9G0Knq', 'Luíza Maria Conceição'),
(30, '99999999999', '2015-02-06 22:47:34', 'posgrad@gmail.com', 'posgrad', '$2a$08$D8RgxAEYmdjmXZkjw7fA8ePUhqROozbFH9pn5gOhPSH7Yz0K2Fowy', 'Amigo Pós'),
(31, '13871725650', '2015-02-06 22:58:19', 'armindo@gmail.com', 'armin', '$2a$08$ffjv/3VlOb4Xu8ayiHEe4e7whvPjlbvwc1MitlMVFYKn0JXtxZsDa', 'Armin Silv'),
(38, '43211590595', '2015-02-12 20:39:36', 'teste@tes.te', 'testando', '$2a$08$9w2p6anX3kMU4Z2Hk/A3nOARyH72KjoW7Ab5Bv9OQFrHdPkCoOGea', 'Aluno Para Teste'),
(40, '70059746890', '2015-02-12 20:49:05', 'teste2@tes.te', 'testando2', '$2a$08$f2CxJkugLp34Ta82hPqDouRm1GOYmviu6N1G.KLro9oxyAB300TtK', 'Aluno Teste Pos'),
(41, '95412923305', '2015-02-27 16:22:21', 'teste@teste.te', 'testinaldo', '$2a$08$Df1K04b5reafdhG/tiKtxe8t62iFV0XH/APguj.Wzaj.Rae6EjOYK', 'Aluno Para Teste'),
(42, '72737474752', '2015-02-27 20:36:39', 'jo@test.es', 'josetes', '$2a$08$ux5wGSsH8aCBkpKMjLnjReVRXQSkqLI9ZvKaBmzsIuiG8jSBw.P.O', 'José Teste');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
