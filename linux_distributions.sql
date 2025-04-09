-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 30, 2025 at 09:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `linux_distributions`
--

-- --------------------------------------------------------

--
-- Table structure for table `distributions`
--

CREATE TABLE `distributions` (
  `id` int(6) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `logo_path` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `distributions`
--

INSERT INTO `distributions` (`id`, `name`, `description`, `logo_path`, `website`, `date_added`) VALUES
(2, 'Arch linux', 'Arch Linux to lekka i elastyczna dystrybucja Linuksa typu rolling-release (ciÄ…gÅ‚ej aktualizacji), przeznaczona dla bardziej zaawansowanych uÅ¼ytkownikÃ³w. Kieruje siÄ™ zasadÄ… KISS (Keep It Simple, Stupid). UÅ¼ytkownik buduje system od podstaw, instalujÄ…c tylko potrzebne pakiety. Znana z doskonaÅ‚ej dokumentacji (Arch Wiki) i menedÅ¼era pakietÃ³w Pacman.', 'img/arch_linux.png', 'https://archlinux.org', '2025-03-30 17:51:47'),
(3, 'NixOs', 'NixOS to dystrybucja Linuksa zbudowana na menedÅ¼erze pakietÃ³w Nix. Wykorzystuje unikalny, deklaratywny model konfiguracji, co zapewnia wysokÄ… odtwarzalnoÅ›Ä‡ i niezawodnoÅ›Ä‡ systemu. UmoÅ¼liwia atomowe aktualizacje i Å‚atwe wycofywanie zmian.', 'img/nixos.png', 'https://nixos.org/', '2025-03-30 18:10:03'),
(4, 'Debian', 'Debian to jedna z najstarszych i najbardziej wpÅ‚ywowych dystrybucji Linuksa. Jest znana ze swojej stabilnoÅ›ci, niezawodnoÅ›ci i silnego przywiÄ…zania do zasad wolnego oprogramowania. UÅ¼ywa systemu zarzÄ…dzania pakietami APT i stanowi bazÄ™ dla wielu innych popularnych dystrybucji, takich jak Ubuntu.', 'img/debian.png', 'https://www.debian.org/', '2025-03-30 18:12:26'),
(5, 'Ubuntu', 'Ubuntu to niezwykle popularna dystrybucja Linuksa oparta na Debianie, znana ze swojej Å‚atwoÅ›ci uÅ¼ytkowania i szerokiej dostÄ™pnoÅ›ci oprogramowania. Oferuje regularne wydania, w tym wersje o dÅ‚ugoterminowym wsparciu (LTS). Jest czÄ™sto polecana poczÄ…tkujÄ…cym uÅ¼ytkownikom Linuksa.', 'img/ubuntu.png', 'https://ubuntu.com/', '2025-03-30 18:13:11'),
(7, 'Gentoo', 'Opis (PL): Gentoo to dystrybucja Linuksa skierowana do zaawansowanych uÅ¼ytkownikÃ³w i entuzjastÃ³w, ktÃ³rzy ceniÄ… sobie maksymalnÄ… kontrolÄ™ i optymalizacjÄ™. Jest to dystrybucja ÅºrÃ³dÅ‚owa â€“ oprogramowanie jest kompilowane bezpoÅ›rednio na maszynie uÅ¼ytkownika za pomocÄ… systemu Portage i flag USE, co pozwala na precyzyjne dostosowanie systemu do wÅ‚asnych potrzeb.', 'img/gentoo.png', 'https://www.gentoo.org/', '2025-03-30 18:23:36'),
(8, 'Alpine Linux', 'Alpine Linux to niezaleÅ¼na, niezwykle lekka dystrybucja Linuksa zorientowana na bezpieczeÅ„stwo. Zbudowana jest w oparciu o musl libc i BusyBox zamiast tradycyjnych narzÄ™dzi GNU. DziÄ™ki minimalnemu rozmiarowi jest bardzo popularna w Å›rodowiskach kontenerowych (np. Docker) i systemach wbudowanych. UÅ¼ywa menedÅ¼era pakietÃ³w apk.', 'img/alpine_linux.png', 'https://alpinelinux.org/', '2025-03-30 18:24:07'),
(9, 'Fedora', 'Fedora to rozwijana przez spoÅ‚ecznoÅ›Ä‡ i sponsorowana przez Red Hat dystrybucja Linuksa, znana z wdraÅ¼ania najnowszych technologii i wolnego oprogramowania. CzÄ™sto stanowi poligon doÅ›wiadczalny dla funkcji wprowadzanych pÃ³Åºniej do Red Hat Enterprise Linux (RHEL). Skupia siÄ™ na innowacyjnoÅ›ci i regularnych, krÃ³tkoterminowych cyklach wydawniczych.', 'img/fedora.png', 'https://fedoraproject.org/', '2025-03-30 18:25:28'),
(10, 'openSUSE', 'openSUSE to wszechstronna dystrybucja Linuksa rozwijana przez spoÅ‚ecznoÅ›Ä‡, znana ze swojego potÄ™Å¼nego narzÄ™dzia konfiguracyjnego YaST. WystÄ™puje w dwÃ³ch gÅ‚Ã³wnych wersjach: Leap (stabilna, z regularnymi wydaniami, dzielÄ…ca bazÄ™ z SUSE Linux Enterprise) oraz Tumbleweed (rolling-release, oferujÄ…ca najnowsze pakiety).', 'img/opensuse.png', 'https://www.opensuse.org/', '2025-03-30 18:26:10'),
(11, 'Linux Mint', 'Linux Mint to popularna dystrybucja bazujÄ…ca na Ubuntu (lub Debianie w wersji LMDE), zaprojektowana z myÅ›lÄ… o Å‚atwoÅ›ci uÅ¼ytkowania i oferujÄ…ca peÅ‚ne wsparcie multimedialne \"prosto z pudeÅ‚ka\". Skupia siÄ™ na dostarczaniu tradycyjnego i eleganckiego Å›rodowiska graficznego, gÅ‚Ã³wnie Cinnamon, MATE i XFCE.', 'img/linux_mint.png', 'https://linuxmint.com/', '2025-03-30 18:26:32'),
(12, 'Manjaro', 'Manjaro to przyjazna dla uÅ¼ytkownika dystrybucja Linuksa bazujÄ…ca na Arch Linux. ÅÄ…czy w sobie zalety modelu rolling-release Arch (dostÄ™p do najnowszego oprogramowania) z Å‚atwiejszÄ… instalacjÄ… i konfiguracjÄ…. Posiada wÅ‚asne repozytoria i narzÄ™dzia uÅ‚atwiajÄ…ce zarzÄ…dzanie systemem, w tym sterownikami.', 'img/manjaro.png', 'https://manjaro.org/', '2025-03-30 18:26:51'),
(13, 'Rocky Linux', 'Rocky Linux to rozwijana przez spoÅ‚ecznoÅ›Ä‡ dystrybucja klasy enterprise, zaprojektowana jako w 100% kompatybilny binarnie zamiennik dla Red Hat Enterprise Linux (RHEL). PowstaÅ‚a po zmianie strategii CentOS. Skupia siÄ™ na stabilnoÅ›ci, dÅ‚ugoterminowym wsparciu i jest przeznaczona gÅ‚Ã³wnie do zastosowaÅ„ serwerowych i korporacyjnych.', 'img/rocky_linux.png', 'https://rockylinux.org/', '2025-03-30 18:27:23'),
(14, 'elementary OS', 'elementary OS to dystrybucja Linuksa bazujÄ…ca na Ubuntu, ktÃ³ra kÅ‚adzie silny nacisk na spÃ³jny wyglÄ…d, prostotÄ™ obsÅ‚ugi i dbaÅ‚oÅ›Ä‡ o szczegÃ³Å‚y interfejsu uÅ¼ytkownika. Rozwija wÅ‚asne Å›rodowisko graficzne Pantheon oraz dedykowane aplikacje, tworzÄ…c spÃ³jny ekosystem.', 'img/elementary_os.png', 'https://elementary.io/', '2025-03-30 18:28:07'),
(15, 'Kali Linux', 'Kali Linux to dystrybucja Linuksa bazujÄ…ca na Debianie, specjalnie zaprojektowana do testÃ³w penetracyjnych, audytÃ³w bezpieczeÅ„stwa i cyfrowej kryminalistyki. Zawiera ogromny zbiÃ³r preinstalowanych narzÄ™dzi sÅ‚uÅ¼Ä…cych do tych celÃ³w. Jest przeznaczona gÅ‚Ã³wnie dla specjalistÃ³w ds. bezpieczeÅ„stwa i etycznych hakerÃ³w.', 'img/kali_linux.png', 'https://www.kali.org/', '2025-03-30 18:31:42'),
(16, 'Pop!_OS', 'Pop!_OS to dystrybucja Linuksa oparta na Ubuntu, rozwijana przez firmÄ™ System76 (producenta komputerÃ³w z Linuksem). Skupia siÄ™ na produktywnoÅ›ci, Å‚atwoÅ›ci uÅ¼ytkowania dla programistÃ³w, twÃ³rcÃ³w i naukowcÃ³w. Oferuje dopracowane Å›rodowisko GNOME z dodatkowymi funkcjami, takimi jak automatyczne kafelkowanie okien (tiling) i dobre wsparcie dla nowoczesnego sprzÄ™tu (zwÅ‚aszcza kart graficznych NVIDIA).', 'img/pop!_os.png', 'https://pop.system76.com/', '2025-03-30 18:32:25'),
(17, 'Zorin OS', 'Zorin OS to dystrybucja bazujÄ…ca na Ubuntu, ktÃ³rej gÅ‚Ã³wnym celem jest uÅ‚atwienie uÅ¼ytkownikom przejÅ›cia z systemÃ³w Windows i macOS na Linuksa. Oferuje znajomy interfejs uÅ¼ytkownika (moÅ¼na wybraÄ‡ wyglÄ…d przypominajÄ…cy Windows lub macOS) oraz preinstalowane oprogramowanie uÅ‚atwiajÄ…ce codziennÄ… pracÄ™. DostÄ™pna jest wersja darmowa (Core) i pÅ‚atne (Pro) z dodatkowymi funkcjami.', 'img/zorin_os.png', 'https://zorin.com/os/', '2025-03-30 18:32:56'),
(18, 'MX Linux', 'MX Linux to dystrybucja Linuksa bazujÄ…ca na stabilnej gaÅ‚Ä™zi Debiana, rozwijana we wspÃ³Å‚pracy ze spoÅ‚ecznoÅ›ciami antiX i byÅ‚ymi deweloperami MEPIS. Cieszy siÄ™ duÅ¼Ä… popularnoÅ›ciÄ… ze wzglÄ™du na swojÄ… stabilnoÅ›Ä‡, dobrÄ… wydajnoÅ›Ä‡ (takÅ¼e na starszym sprzÄ™cie), Å‚atwoÅ›Ä‡ obsÅ‚ugi i zestaw unikalnych narzÄ™dzi konfiguracyjnych (MX Tools). DomyÅ›lnym Å›rodowiskiem graficznym jest XFCE, ale dostÄ™pne sÄ… teÅ¼ wersje z KDE i Fluxbox.', 'img/mx_linux.png', 'https://mxlinux.org/', '2025-03-30 18:33:32'),
(19, 'EndeavourOS', 'EndeavourOS to dystrybucja typu rolling-release bazujÄ…ca na Arch Linux, ktÃ³ra stawia sobie za cel bycie Å‚atwiejszÄ… w instalacji i poczÄ…tkowej konfiguracji niÅ¼ czysty Arch, ale jednoczeÅ›nie pozostajÄ…cÄ… blisko jego filozofii. Oferuje przyjazny instalator graficzny (Calamares) z moÅ¼liwoÅ›ciÄ… wyboru Å›rodowiska graficznego i minimalnÄ… liczbÄ… preinstalowanych aplikacji, zachÄ™cajÄ…c uÅ¼ytkownika do samodzielnej budowy systemu.', 'img/endeavouros.png', 'https://endeavouros.com/', '2025-03-30 18:35:06'),
(20, 'CentOS Stream', 'CentOS Stream to dystrybucja Linuksa rozwijana przez Red Hat, ktÃ³ra sÅ‚uÅ¼y jako publiczna platforma rozwojowa dla przyszÅ‚ych wersji Red Hat Enterprise Linux (RHEL). Jest to dystrybucja typu rolling-release (w kontekÅ›cie rozwoju RHEL), znajdujÄ…ca siÄ™ pomiÄ™dzy FedorÄ… (poligon doÅ›wiadczalny) a stabilnym RHEL. Jest przeznaczona dla deweloperÃ³w i partnerÃ³w chcÄ…cych Å›ledziÄ‡ i wspÃ³Å‚tworzyÄ‡ przyszÅ‚oÅ›Ä‡ RHEL.', 'img/centos_stream.png', 'https://www.centos.org/centos-stream/', '2025-03-30 18:35:47'),
(21, 'Red Hat Enterprise Linux (RHEL)', 'Red Hat Enterprise Linux to wiodÄ…ca komercyjna dystrybucja Linuksa przeznaczona dla przedsiÄ™biorstw. Oferuje wyjÄ…tkowÄ… stabilnoÅ›Ä‡, dÅ‚ugoterminowe wsparcie techniczne (nawet 10 lat), certyfikacje sprzÄ™towe i programowe oraz rozbudowane funkcje bezpieczeÅ„stwa. DostÄ™pna jest na zasadzie subskrypcji, ktÃ³ra obejmuje dostÄ™p do oprogramowania, aktualizacji i wsparcia. Stanowi podstawÄ™ dla wielu innych dystrybucji (np. Rocky Linux, AlmaLinux).', 'img/red_hat_enterprise_linux_(rhel).png', 'https://www.redhat.com/en/technologies/linux-platforms/enterprise-linux', '2025-03-30 18:37:31'),
(22, 'KDE neon', 'KDE neon to dystrybucja Linuksa rozwijana przez zespÃ³Å‚ KDE, bazujÄ…ca na stabilnych wydaniach Ubuntu LTS (Long Term Support). Jej gÅ‚Ã³wnym celem jest dostarczanie uÅ¼ytkownikom najnowszych wersji Å›rodowiska graficznego KDE Plasma oraz powiÄ…zanych aplikacji KDE bezpoÅ›rednio od twÃ³rcÃ³w. Jest to doskonaÅ‚y wybÃ³r dla entuzjastÃ³w KDE, ktÃ³rzy chcÄ… mieÄ‡ stabilnÄ… podstawÄ™ systemu i jednoczeÅ›nie najÅ›wieÅ¼sze oprogramowanie KDE.', 'img/kde_neon.png', 'https://neon.kde.org/', '2025-03-30 18:38:03'),
(23, 'AlmaLinux', 'AlmaLinux to kolejna, rozwijana przez spoÅ‚ecznoÅ›Ä‡ i wspierana przez CloudLinux Inc., darmowa dystrybucja Linuksa klasy enterprise. Podobnie jak Rocky Linux, jest zaprojektowana jako w 100% kompatybilny binarnie zamiennik dla Red Hat Enterprise Linux (RHEL). PowstaÅ‚a w odpowiedzi na zmiany w strategii CentOS i oferuje stabilnÄ…, bezpiecznÄ… i darmowÄ… platformÄ™ dla zastosowaÅ„ serwerowych i korporacyjnych.', 'img/almalinux.png', 'https://almalinux.org/', '2025-03-30 18:40:06'),
(24, 'Kubuntu', 'Kubuntu to oficjalna pochodna dystrybucji Ubuntu, ktÃ³ra zamiast domyÅ›lnego Å›rodowiska GNOME uÅ¼ywa Å›rodowiska graficznego KDE Plasma. ÅÄ…czy Å‚atwoÅ›Ä‡ uÅ¼ytkowania i szerokÄ… bazÄ™ oprogramowania Ubuntu z bogactwem funkcji i moÅ¼liwoÅ›ciami konfiguracji oferowanymi przez KDE Plasma. Wydawana jest w tych samych cyklach co Ubuntu, w tym wersje LTS.', 'img/kubuntu.png', ' https://kubuntu.org/', '2025-03-30 18:40:31'),
(26, 'Solus', 'Solus to niezaleÅ¼na dystrybucja Linuksa budowana od podstaw (nie bazuje na Debianie, Fedorze czy Archu). Wykorzystuje model \"curated rolling release\", co oznacza ciÄ…gÅ‚e aktualizacje, ale z pewnym opÃ³Åºnieniem i testowaniem, aby zapewniÄ‡ stabilnoÅ›Ä‡. Znana jest z wÅ‚asnego, eleganckiego Å›rodowiska graficznego Budgie (choÄ‡ oferuje teÅ¼ wersje z GNOME, MATE i KDE Plasma) oraz menedÅ¼era pakietÃ³w eopkg. Skupia siÄ™ na zapewnieniu dobrego doÅ›wiadczenia na komputerach osobistych.', 'img/solus.png', 'https://getsol.us/', '2025-03-30 18:55:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `distributions`
--
ALTER TABLE `distributions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `distributions`
--
ALTER TABLE `distributions`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
