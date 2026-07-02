-- MyDndParty legacy database schema - versione sanificata
-- Generato dal dump Sql1777135_3.sql del 2026-07-02.
-- Sono stati rimossi gli INSERT di tabelle contenenti dati utente, token, log, gruppi, personaggi, inventario, monete e combattimenti.
-- Conservati gli INSERT solo per tabelle di dominio/lookup utili alla ricostruzione dell'app.

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 31.11.39.219:3306
-- Creato il: Lug 02, 2026 alle 12:41
-- Versione del server: 8.0.44-35
-- Versione PHP: 8.0.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Sql1777135_3`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `cfgLingua`
--

CREATE TABLE `cfgLingua` (
  `id` int NOT NULL,
  `idLingua` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `cfgLingua`
--

INSERT INTO `cfgLingua` (`id`, `idLingua`) VALUES
(1, 'ITA');

-- --------------------------------------------------------

--
-- Struttura della tabella `cfgSistema`
--

CREATE TABLE `cfgSistema` (
  `id` int NOT NULL,
  `sistema` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `cfgSistema`
--

INSERT INTO `cfgSistema` (`id`, `sistema`) VALUES
(1, 'D&D');

-- --------------------------------------------------------

--
-- Struttura della tabella `cfgUtenti`
--

CREATE TABLE `cfgUtenti` (
  `id` int NOT NULL,
  `idUtente` int NOT NULL,
  `dado` int NOT NULL,
  `lingua` int NOT NULL,
  `sistema` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `cfgUtenti`
--

-- INSERT INTO `cfgUtenti` rimosso dalla versione sanificata: dati runtime/utente non adatti a repository pubblico.

-- --------------------------------------------------------

--
-- Struttura della tabella `classi`
--

CREATE TABLE `classi` (
  `id` int NOT NULL,
  `classe` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `classi`
--

INSERT INTO `classi` (`id`, `classe`) VALUES
(1, 'Acrobata'),
(2, 'Assassino'),
(3, 'Barbaro'),
(4, 'Bardo'),
(5, 'Cavaliere'),
(6, 'Drow'),
(7, 'Druido'),
(8, 'Duergar'),
(9, 'Gnomo'),
(10, 'Illusionista'),
(11, 'Mezzelfo'),
(12, 'Mezzorco'),
(13, 'Paladino'),
(14, 'Ranger'),
(15, 'Svirfneblin'),
(16, 'Lama iettatrice'),
(17, 'Ladro magico'),
(18, 'Mago da guerra'),
(19, 'Guaritore'),
(20, 'Mago delle armature'),
(21, 'Evocatore'),
(22, 'Metamorfo'),
(23, 'Marionettista'),
(24, 'Psicocineta'),
(25, 'Guerriero'),
(26, 'Mago'),
(27, 'Chierico'),
(28, 'Ladro'),
(29, 'Ranger'),
(30, 'Paladino'),
(31, 'Bardo'),
(32, 'Druido'),
(33, 'Stregone'),
(34, 'Monaco'),
(35, 'Barbaro'),
(36, 'Incantatore'),
(37, 'Warlock'),
(38, 'Artificiere'),
(39, 'Spadaccino'),
(40, 'Cacciatore di mostri'),
(41, 'Cacciatore'),
(42, 'Pistolero'),
(43, 'Psionico'),
(44, 'Alchimista'),
(45, 'Avventuriero'),
(46, 'Cavaliere'),
(47, 'Combattente Arcano'),
(48, 'Maestro delle Bestie'),
(49, 'Cavaliere Dragone'),
(50, 'Ingegnere'),
(51, 'Inquisitore'),
(52, 'Ladro delle Ombre'),
(53, 'Ingegnere di Guerra'),
(54, 'Samurai'),
(55, 'Mago del Tempo'),
(56, 'Aberrante'),
(57, 'Elementalista'),
(58, 'Signore delle Tempeste'),
(59, 'Mago della Guerra'),
(60, 'Maestro delle Maschere'),
(61, 'Pugilatore'),
(62, 'Corsaro'),
(63, 'Agente Segreto'),
(64, 'Mangiatore di Anime'),
(65, 'Erudito'),
(66, 'Mistico'),
(67, 'Nobile'),
(68, 'Bandito'),
(69, 'Cittadino'),
(70, 'Avventuriero Errante'),
(71, 'Monarca'),
(72, 'Geomante'),
(73, 'Illuminato'),
(74, 'Nomade'),
(75, 'Minatore'),
(76, 'Veggente'),
(77, 'Guardiano'),
(78, 'Corvo di Guerra'),
(79, 'Maestro delle Ombre'),
(80, 'Arcanista'),
(81, 'Incantatore di Sangue'),
(82, 'Elettricista'),
(83, 'Demolitore'),
(84, 'Stregone Selvaggio'),
(85, 'Chierico del Sole'),
(86, 'Stregone dell\'Abisso'),
(87, 'Mistico dell\'Ombra'),
(88, 'Uomo Bestia'),
(89, 'Mago Incantatore'),
(90, 'Elementale'),
(91, 'Psicoduelista'),
(92, 'Templare'),
(93, 'Mago Necromante'),
(94, 'Mercenario'),
(95, 'Guardiano degli Spiriti'),
(96, 'Mago Avventuriero'),
(97, 'Mago di Battaglia'),
(98, 'Spadaccino Arcano'),
(99, 'Druido del Circolo'),
(100, 'Mistico del Sangue'),
(101, 'Maestro delle Bestie Druidiche'),
(102, 'Mago del Tempo Divino'),
(103, 'Paladino del Dolore'),
(104, 'Ladrone d\'Anima'),
(105, 'Lupo Solitario'),
(106, 'Ingegnere Tinkerer'),
(107, 'Signore dei Mostri'),
(108, 'Chierico del Conflitto'),
(109, 'Inquisitore del Caos'),
(110, 'Guardia delle Ombre'),
(111, 'Pistolaire'),
(112, 'Custode dei Segreti'),
(113, 'Mago Ombra'),
(114, 'Magia Selvaggia');

-- --------------------------------------------------------

--
-- Struttura della tabella `combattimento`
--

CREATE TABLE `combattimento` (
  `idCombattimento` int NOT NULL,
  `idGruppo` int NOT NULL,
  `idPersonaggio` int DEFAULT NULL,
  `personaggio` varchar(50) NOT NULL,
  `iniziativa` int DEFAULT '0',
  `bonusIniziativa` int NOT NULL,
  `lento` varchar(1) DEFAULT NULL,
  `fight` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `combattimento`
--

-- INSERT INTO `combattimento` rimosso dalla versione sanificata: dati runtime/utente non adatti a repository pubblico.

-- --------------------------------------------------------

--
-- Struttura della tabella `compagnia`
--

CREATE TABLE `compagnia` (
  `id` int NOT NULL,
  `nomePG` varchar(50) NOT NULL,
  `nomeGiocatore` varchar(50) NOT NULL,
  `bonusIniziativa` int DEFAULT '0',
  `Classe` varchar(50) NOT NULL,
  `Razza` varchar(50) NOT NULL,
  `Motto` text,
  `idUtente` int NOT NULL,
  `idGruppo` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `compagnia`
--

-- INSERT INTO `compagnia` rimosso dalla versione sanificata: dati runtime/utente non adatti a repository pubblico.

-- --------------------------------------------------------

--
-- Struttura della tabella `dadoIniziativa`
--

CREATE TABLE `dadoIniziativa` (
  `id` int NOT NULL,
  `dado` int NOT NULL,
  `descrizione` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `predefinita` varchar(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `dadoIniziativa`
--

INSERT INTO `dadoIniziativa` (`id`, `dado`, `descrizione`, `predefinita`) VALUES
(1, 4, 'D4', 'N'),
(2, 6, 'D6', 'N'),
(3, 8, 'D8', 'N'),
(4, 10, 'D10', 'S'),
(5, 12, 'D12', 'N'),
(6, 20, 'D20', 'N'),
(18, 100, 'D100', 'N');

-- --------------------------------------------------------

--
-- Struttura della tabella `effetti`
--

CREATE TABLE `effetti` (
  `id` int NOT NULL,
  `idCombattimento` int NOT NULL,
  `effetto` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `round` int NOT NULL,
  `permanente` varchar(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `effetti`
--

-- INSERT INTO `effetti` rimosso dalla versione sanificata: dati runtime/utente non adatti a repository pubblico.

-- --------------------------------------------------------

--
-- Struttura della tabella `gruppi`
--

CREATE TABLE `gruppi` (
  `id` int NOT NULL,
  `idUser` int NOT NULL,
  `Gruppo` varchar(50) NOT NULL,
  `Attivo` varchar(1) NOT NULL DEFAULT 'N',
  `Appunti` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `gruppi`
--

-- INSERT INTO `gruppi` rimosso dalla versione sanificata: dati runtime/utente non adatti a repository pubblico.

-- --------------------------------------------------------

--
-- Struttura della tabella `inventario`
--

CREATE TABLE `inventario` (
  `id` int NOT NULL,
  `des` text NOT NULL,
  `ide` varchar(2) DEFAULT NULL,
  `qta` int NOT NULL,
  `val` int DEFAULT NULL,
  `categoria` varchar(50) NOT NULL,
  `note` text,
  `idUtente` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `inventario`
--

-- INSERT INTO `inventario` rimosso dalla versione sanificata: dati runtime/utente non adatti a repository pubblico.

-- --------------------------------------------------------

--
-- Struttura della tabella `log`
--

CREATE TABLE `log` (
  `id` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fileName` varchar(50) NOT NULL,
  `message` text,
  `severity` int NOT NULL,
  `user_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `log`
--

-- INSERT INTO `log` rimosso dalla versione sanificata: dati runtime/utente non adatti a repository pubblico.

-- --------------------------------------------------------

--
-- Struttura della tabella `logSeverity`
--

CREATE TABLE `logSeverity` (
  `id` int NOT NULL,
  `severity` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `logSeverity`
--

INSERT INTO `logSeverity` (`id`, `severity`) VALUES
(1, 'Error'),
(2, 'Critical'),
(3, 'Alert'),
(4, 'Message');

-- --------------------------------------------------------

--
-- Struttura della tabella `monete`
--

CREATE TABLE `monete` (
  `id` int NOT NULL,
  `idGruppo` int DEFAULT NULL,
  `idMoneta` int NOT NULL,
  `quantita` int DEFAULT '0',
  `quantitaDeposito` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `monete`
--

-- INSERT INTO `monete` rimosso dalla versione sanificata: dati runtime/utente non adatti a repository pubblico.

-- --------------------------------------------------------

--
-- Struttura della tabella `razze`
--

CREATE TABLE `razze` (
  `id` int NOT NULL,
  `razza` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `razze`
--

INSERT INTO `razze` (`id`, `razza`) VALUES
(1, 'Drow'),
(2, 'Duergar'),
(3, 'Elfo'),
(4, 'Gnomo'),
(5, 'Halfling'),
(6, 'Mezzelfo'),
(7, 'Mezzorco'),
(8, 'Nano'),
(9, 'Svirfneblin'),
(10, 'Umano'),
(11, 'Bugbear'),
(12, 'Neanderthal'),
(13, 'Gnoll'),
(14, 'Hobgoblin'),
(15, 'Uomini Lucertola'),
(16, 'Ogre'),
(17, 'Orchetti'),
(18, 'Trogloditi'),
(19, 'Sciamani del Thar'),
(20, 'Centauro'),
(21, 'Driade'),
(22, 'Fauno'),
(23, 'Kenku'),
(24, 'Treant'),
(25, 'Draghetto Silvano'),
(26, 'Folletti e Spiritelli'),
(27, 'Lupo mannaro'),
(28, 'Orso mannaro'),
(29, 'Cinghiale mannaro'),
(30, 'Volpe mannara'),
(31, 'Topo mannaro'),
(32, 'Tigre mannara'),
(33, 'Giaguaro mannaro'),
(34, 'Mezzo drago'),
(35, 'Mezzo celestiale'),
(36, 'Mezzo immondo');

-- --------------------------------------------------------

--
-- Struttura della tabella `resetPassword`
--

CREATE TABLE `resetPassword` (
  `id` int NOT NULL,
  `idUtente` int NOT NULL,
  `token` varchar(50) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `resetPassword`
--

-- INSERT INTO `resetPassword` rimosso dalla versione sanificata: dati runtime/utente non adatti a repository pubblico.

-- --------------------------------------------------------

--
-- Struttura della tabella `round`
--

CREATE TABLE `round` (
  `idGruppo` int NOT NULL,
  `round` int NOT NULL,
  `id` int NOT NULL,
  `attivo` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `round`
--

-- INSERT INTO `round` rimosso dalla versione sanificata: dati runtime/utente non adatti a repository pubblico.

-- --------------------------------------------------------

--
-- Struttura della tabella `tipoMonete`
--

CREATE TABLE `tipoMonete` (
  `id` int NOT NULL,
  `moneta` varchar(50) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `rapporto` decimal(10,5) NOT NULL,
  `peso` decimal(10,5) NOT NULL,
  `gemma` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `tipoMonete`
--

INSERT INTO `tipoMonete` (`id`, `moneta`, `tipo`, `rapporto`, `peso`, `gemma`) VALUES
(1, 'PLATINO', 'D&D', 5.00000, 0.06000, 'N'),
(2, 'ORO', 'D&D', 1.00000, 0.02000, 'N'),
(3, 'ELECTRUM', 'D&D', 0.50000, 0.02000, 'N'),
(4, 'ARGENTO', 'D&D', 0.10000, 0.01000, 'N'),
(5, 'RAME', 'D&D', 0.01000, 0.01500, 'N'),
(6, 'GEMME', 'D&D', 1.00000, 0.00000, 'S');

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `validata` varchar(1) NOT NULL DEFAULT 'N',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `token` varchar(25) NOT NULL,
  `admin` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `utenti`
--

-- INSERT INTO `utenti` rimosso dalla versione sanificata: dati runtime/utente non adatti a repository pubblico.

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `cfgLingua`
--
ALTER TABLE `cfgLingua`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `cfgSistema`
--
ALTER TABLE `cfgSistema`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `cfgUtenti`
--
ALTER TABLE `cfgUtenti`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `classi`
--
ALTER TABLE `classi`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `combattimento`
--
ALTER TABLE `combattimento`
  ADD PRIMARY KEY (`idCombattimento`);

--
-- Indici per le tabelle `compagnia`
--
ALTER TABLE `compagnia`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `dadoIniziativa`
--
ALTER TABLE `dadoIniziativa`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `effetti`
--
ALTER TABLE `effetti`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `gruppi`
--
ALTER TABLE `gruppi`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `logSeverity`
--
ALTER TABLE `logSeverity`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `monete`
--
ALTER TABLE `monete`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `razze`
--
ALTER TABLE `razze`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `resetPassword`
--
ALTER TABLE `resetPassword`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `tipoMonete`
--
ALTER TABLE `tipoMonete`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `cfgLingua`
--
ALTER TABLE `cfgLingua`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `cfgSistema`
--
ALTER TABLE `cfgSistema`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `cfgUtenti`
--
ALTER TABLE `cfgUtenti`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT per la tabella `classi`
--
ALTER TABLE `classi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT per la tabella `combattimento`
--
ALTER TABLE `combattimento`
  MODIFY `idCombattimento` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3811;

--
-- AUTO_INCREMENT per la tabella `compagnia`
--
ALTER TABLE `compagnia`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT per la tabella `dadoIniziativa`
--
ALTER TABLE `dadoIniziativa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT per la tabella `effetti`
--
ALTER TABLE `effetti`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT per la tabella `gruppi`
--
ALTER TABLE `gruppi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT per la tabella `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT per la tabella `log`
--
ALTER TABLE `log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=195;

--
-- AUTO_INCREMENT per la tabella `logSeverity`
--
ALTER TABLE `logSeverity`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `monete`
--
ALTER TABLE `monete`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=217;

--
-- AUTO_INCREMENT per la tabella `razze`
--
ALTER TABLE `razze`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT per la tabella `resetPassword`
--
ALTER TABLE `resetPassword`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT per la tabella `tipoMonete`
--
ALTER TABLE `tipoMonete`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
