-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 23, 2025 at 09:49 AM
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
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `username`, `email`, `password`, `date_added`) VALUES
(1, 'Tymon3310', 'kopczynski.tymon@gmail.com', '$2y$10$wVSExLuYShZpumLuR.nTtOrjjICrNHD/Fhhyv5IcTKobuP6ySEFVm', '2025-04-10 09:10:24');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `distro_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `comment` text NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `distro_id`, `user_id`, `username`, `comment`, `date_added`) VALUES
(7, 12, 2, 'NaN', 'a', '2025-04-09 12:50:38'),
(8, 12, 1, 'Tymon3310', 'test', '2025-04-14 07:26:38');

-- --------------------------------------------------------

--
-- Table structure for table `distributions`
--

CREATE TABLE `distributions` (
  `id` int(6) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` longtext NOT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `distributions`
--

INSERT INTO `distributions` (`id`, `name`, `description`, `youtube`, `logo_path`, `website`, `added_by`, `date_added`) VALUES
(2, 'Arch linux', 'Arch Linux to lekka i elastyczna dystrybucja Linuksa typu rolling-release (ciągłej aktualizacji), przeznaczona dla bardziej zaawansowanych użytkowników. Kieruje się zasadą KISS (Keep It Simple, Stupid). Użytkownik buduje system od podstaw, instalując tylko potrzebne pakiety. Znana z doskonałej dokumentacji (Arch Wiki) i menedżera pakietów Pacman oraz repozytioria użytkowników AUR.', '', 'img/arch_linux.png', 'https://archlinux.org', 1, '2025-03-30 17:51:47'),
(3, 'NixOs', 'NixOS to dystrybucja Linuksa zbudowana na menedżerze pakietów Nix. Wykorzystuje unikalny, deklaratywny model konfiguracji, co zapewnia wysoką odtwarzalność i niezawodność systemu. Umożliwia atomowe aktualizacje i łatwe wycofywanie zmian.', NULL, 'img/nixos.png', 'https://nixos.org/', 1, '2025-03-30 18:10:03'),
(4, 'Debian', 'Debian to jedna z najstarszych i najbardziej wpływowych dystrybucji Linuksa. Jest znana ze swojej stabilności, niezawodności i silnego przywiązania do zasad wolnego oprogramowania. Używa systemu zarządzania pakietami APT i stanowi bazę dla wielu innych popularnych dystrybucji, takich jak Ubuntu.', NULL, 'img/debian.png', 'https://www.debian.org/', 1, '2025-03-30 18:12:26'),
(5, 'Ubuntu', 'Ubuntu to niezwykle popularna dystrybucja Linuksa oparta na Debianie, znana ze swojej łatwości użytkowania i szerokiej dostępności oprogramowania. Oferuje regularne wydania, w tym wersje o długoterminowym wsparciu (LTS). Jest często polecana początkującym użytkownikom Linuksa.', NULL, 'img/ubuntu.png', 'https://ubuntu.com/', 1, '2025-03-30 18:13:11'),
(7, 'Gentoo', 'Gentoo to dystrybucja Linuksa skierowana do zaawansowanych użytkowników i entuzjastów, którzy cenią sobie maksymalną kontrolę i optymalizację. Jest to dystrybucja źródłowa – oprogramowanie jest kompilowane bezpośrednio na maszynie użytkownika za pomocą systemu Portage i flag USE, co pozwala na precyzyjne dostosowanie systemu do własnych potrzeb.', NULL, 'img/gentoo.png', 'https://www.gentoo.org/', 1, '2025-03-30 18:23:36'),
(8, 'Alpine Linux', 'Alpine Linux to niezależna, niezwykle lekka dystrybucja Linuksa zorientowana na bezpieczeństwo. Zbudowana jest w oparciu o musl libc i BusyBox zamiast tradycyjnych narzędzi GNU. Dzięki minimalnemu rozmiarowi jest bardzo popularna w środowiskach kontenerowych (np. Docker) i systemach wbudowanych. Używa menedżera pakietów apk.', NULL, 'img/alpine_linux.png', 'https://alpinelinux.org/', 1, '2025-03-30 18:24:07'),
(9, 'Fedora', 'Fedora to rozwijana przez społeczność i sponsorowana przez Red Hat dystrybucja Linuksa, znana z wdrażania najnowszych technologii i wolnego oprogramowania. Często stanowi poligon doświadczalny dla funkcji wprowadzanych później do Red Hat Enterprise Linux (RHEL). Skupia się na innowacyjności i regularnych, krótkoterminowych cyklach wydawniczych.', 'https://www.youtube.com/watch?v=Lpm_ZE4trJw', 'img/fedora.png', 'https://fedoraproject.org/', 1, '2025-03-30 18:25:28'),
(10, 'openSUSE', 'openSUSE to wszechstronna dystrybucja Linuksa rozwijana przez społeczność, znana ze swojego potężnego narzędzia konfiguracyjnego YaST. Występuje w dwóch głównych wersjach: Leap (stabilna, z regularnymi wydaniami, dzieląca bazę z SUSE Linux Enterprise) oraz Tumbleweed (rolling-release, oferująca najnowsze pakiety).', NULL, 'img/opensuse.png', 'https://www.opensuse.org/', 1, '2025-03-30 18:26:10'),
(11, 'Linux Mint', 'Linux Mint to popularna dystrybucja bazująca na Ubuntu (lub Debianie w wersji LMDE), zaprojektowana z myślą o łatwości użytkowania i oferująca pełne wsparcie multimedialne \"prosto z pudełka\". Skupia się na dostarczaniu tradycyjnego i eleganckiego środowiska graficznego, głównie Cinnamon, MATE i XFCE.', 'https://www.youtube.com/watch?v=-LUaW7e5zQ8', 'img/linux_mint.png', 'https://linuxmint.com/', 1, '2025-03-30 18:26:32'),
(12, 'Manjaro', 'Manjaro to przyjazna dla użytkownika dystrybucja Linuksa bazująca na Arch Linux. Łączy w sobie zalety modelu rolling-release Arch (dostęp do najnowszego oprogramowania) z łatwiejszą instalacją i konfiguracją. Posiada własne repozytoria i narzędzia ułatwiające zarządzanie systemem, w tym sterownikami.', 'https://www.youtube.com/watch?v=jWQ_q79ErRg', 'img/manjaro.png', 'https://manjaro.org/', 1, '2025-03-30 18:26:51'),
(13, 'Rocky Linux', 'Rocky Linux to rozwijana przez społeczność dystrybucja klasy enterprise, zaprojektowana jako w 100% kompatybilny binarnie zamiennik dla Red Hat Enterprise Linux (RHEL). Powstała po zmianie strategii CentOS. Skupia się na stabilności, długoterminowym wsparciu i jest przeznaczona głównie do zastosowań serwerowych i korporacyjnych.', NULL, 'img/rocky_linux.png', 'https://rockylinux.org/', 1, '2025-03-30 18:27:23'),
(14, 'elementary OS', 'elementary OS to dystrybucja Linuksa bazująca na Ubuntu, która  kładzie silny nacisk na spójny wygląd, prostotę obsługi i dbałość o szczegóły interfejsu użytkownika. Rozwija własne środowisko graficzne Pantheon oraz dedykowane aplikacje, tworząc spójny ekosystem.', NULL, 'img/elementary_os.png', 'https://elementary.io/', 1, '2025-03-30 18:28:07'),
(15, 'Kali Linux', 'Kali Linux to dystrybucja Linuksa bazująca na Debianie, specjalnie zaprojektowana do testów penetracyjnych, audytów bezpieczeństwa i cyfrowej kryminalistyki. Zawiera ogromny zbiór preinstalowanych narzędzi służących do tych celów. Jest przeznaczona głównie dla specjalistów ds. bezpieczeństwa i etycznych hakerów.', NULL, 'img/kali_linux.png', 'https://www.kali.org/', 1, '2025-03-30 18:31:42'),
(16, 'Pop!_OS', 'Pop!_OS to dystrybucja Linuksa oparta na Ubuntu, rozwijana przez firmę System76 (producenta komputerów z Linuksem). Skupia się na produktywności, łatwości użytkowania dla programistów, twórców i naukowców. Oferuje dopracowane środowisko GNOME z dodatkowymi funkcjami, takimi jak automatyczne kafelkowanie okien (tiling) i dobre wsparcie dla nowoczesnego sprzętu (zwłaszcza kart graficznych NVIDIA).', NULL, 'img/pop!_os.png', 'https://pop.system76.com/', 1, '2025-03-30 18:32:25'),
(17, 'Zorin OS', 'Zorin OS to dystrybucja bazująca na Ubuntu, której głównym celem jest ułatwienie użytkownikom przejścia z systemów Windows i macOS na Linuksa. Oferuje znajomy interfejs użytkownika (można wybrać wygląd przypominający Windows lub macOS) oraz preinstalowane oprogramowanie ułatwiające codzienną pracę. Dostępna jest wersja darmowa (Core) i płatne (Pro) z dodatkowymi funkcjami.', NULL, 'img/zorin_os.png', 'https://zorin.com/os/', 1, '2025-03-30 18:32:56'),
(18, 'MX Linux', 'MX Linux to dystrybucja Linuksa bazująca na stabilnej gałęzi Debiana, rozwijana we współpracy ze społecznościami antiX i byłymi deweloperami MEPIS. Cieszy się dużą popularnością ze względu na swoją stabilność, dobrą wydajność (także na starszym sprzęcie), łatwość obsługi i zestaw unikalnych narzędzi konfiguracyjnych (MX Tools). Domyślnym środowiskiem graficznym jest XFCE, ale dostępne są też wersje z KDE i Fluxbox.', NULL, 'img/mx_linux.png', 'https://mxlinux.org/', 1, '2025-03-30 18:33:32'),
(19, 'EndeavourOS', 'EndeavourOS to dystrybucja typu rolling-release bazująca na Arch Linux, która stawia sobie za cel bycie łatwiejszą w instalacji i początkowej konfiguracji niż czysty Arch, ale jednocześnie pozostającą blisko jego filozofii. Oferuje przyjazny instalator graficzny (Calamares) z możliwością wyboru środowiska graficznego i minimalną liczbą preinstalowanych aplikacji, zachęcając użytkownika do samodzielnej budowy systemu.', NULL, 'img/endeavouros.png', 'https://endeavouros.com/', 1, '2025-03-30 18:35:06'),
(20, 'CentOS Stream', 'CentOS Stream to dystrybucja Linuksa rozwijana przez Red Hat, która służy jako publiczna platforma rozwojowa dla przyszłych wersji Red Hat Enterprise Linux (RHEL). Jest to dystrybucja typu rolling-release (w kontekście rozwoju RHEL), znajdująca się pomiędzy Fedorą (poligon doświadczalny) a stabilnym RHEL. Jest przeznaczona dla deweloperów i partnerów chcących śledzić i współtworzyć przyszłość RHEL.', NULL, 'img/centos_stream.png', 'https://www.centos.org/centos-stream/', 1, '2025-03-30 18:35:47'),
(21, 'Red Hat Enterprise Linux (RHEL)', 'Red Hat Enterprise Linux to wiodąca komercyjna dystrybucja Linuksa przeznaczona dla przedsiębiorstw. Oferuje wyjątkową stabilność, długoterminowe wsparcie techniczne (nawet 10 lat), certyfikacje sprzętowe i programowe oraz rozbudowane funkcje bezpieczeństwa. Dostępna jest na zasadzie subskrypcji, która obejmuje dostęp do oprogramowania, aktualizacji i wsparcia. Stanowi podstawę dla wielu innych dystrybucji (np. Rocky Linux, AlmaLinux).', NULL, 'img/red_hat_enterprise_linux_(rhel).png', 'https://www.redhat.com/en/technologies/linux-platforms/enterprise-linux', 1, '2025-03-30 18:37:31'),
(22, 'KDE neon', 'KDE neon to dystrybucja Linuksa rozwijana przez zespół KDE, bazująca na stabilnych wydaniach Ubuntu LTS (Long Term Support). Jej głównym celem jest dostarczanie użytkownikom najnowszych wersji środowiska graficznego KDE Plasma oraz powiązanych aplikacji KDE bezpośrednio od twórców. Jest to doskonały wybór dla entuzjastów KDE, którzy chcą mieć stabilną podstawę systemu i jednocześnie najświeższe oprogramowanie KDE.', NULL, 'img/kde_neon.png', 'https://neon.kde.org/', 1, '2025-03-30 18:38:03'),
(23, 'AlmaLinux', 'AlmaLinux to kolejna, rozwijana przez społeczność i wspierana przez CloudLinux Inc., darmowa dystrybucja Linuksa klasy enterprise. Podobnie jak Rocky Linux, jest zaprojektowana jako w 100% kompatybilny binarnie zamiennik dla Red Hat Enterprise Linux (RHEL). Powstała w odpowiedzi na zmiany w strategii CentOS i oferuje stabilną, bezpieczną i darmową platformę dla zastosowań serwerowych i korporacyjnych.', NULL, 'img/almalinux.png', 'https://almalinux.org/', 1, '2025-03-30 18:40:06'),
(24, 'Kubuntu', 'Kubuntu to oficjalna pochodna dystrybucji Ubuntu, która zamiast domyślnego środowiska GNOME używa środowiska graficznego KDE Plasma. Łączy łatwość użytkowania i szeroką bazę oprogramowania Ubuntu z bogactwem funkcji i możliwościami konfiguracji oferowanymi przez KDE Plasma. Wydawana jest w tych samych cyklach co Ubuntu, w tym wersje LTS.', NULL, 'img/kubuntu.png', ' https://kubuntu.org/', 1, '2025-03-30 18:40:31'),
(26, 'Solus', 'Solus to niezależna dystrybucja Linuksa budowana od podstaw (nie bazuje na Debianie, Fedorze czy Archu). Wykorzystuje model \"curated rolling release\", co oznacza ciągłe aktualizacje, ale z pewnym opóźnieniem i testowaniem, aby zapewnić stabilność. Znana jest z własnego, eleganckiego środowiska graficznego Budgie (choć oferuje też wersje z GNOME, MATE i KDE Plasma) oraz menedżera pakietów eopkg. Skupia się na zapewnieniu dobrego doświadczenia na komputerach osobistych.', NULL, 'img/solus.png', 'https://getsol.us/', 1, '2025-03-30 18:55:36'),
(32, 'Talis', 'Tails to dystrybucja Linuksa typu \"live\", zaprojektowana z myślą o maksymalnej ochronie prywatności i anonimowości. Uruchamiana jest z nośnika USB lub DVD i nie pozostawia żadnych śladów na komputerze, na którym jest używana. Cały ruch internetowy jest automatycznie kierowany przez sieć Tor. Zawiera zestaw preinstalowanych narzędzi kryptograficznych i do bezpiecznej komunikacji.', NULL, 'img/talis.png', 'https://tails.net/', 1, '2025-04-16 08:56:22'),
(33, 'Garuda Linux', 'Garuda Linux to dystrybucja Linuksa bazująca na Arch Linux, która koncentruje się na wysokiej wydajności i atrakcyjnym wyglądzie \"prosto z pudełka\". Oferuje zoptymalizowane jądro (linux-zen), system plików BTRFS z automatycznymi migawkami (snapshots) oraz różne narzędzia ułatwiające zarządzanie systemem i grami. Dostępna jest w wielu wersjach z różnymi, mocno dostosowanymi wizualnie środowiskami graficznymi (głównie KDE Plasma).', NULL, 'img/garuda_linux.png', 'https://garudalinux.org/', 1, '2025-04-16 08:57:15'),
(34, 'Slackware', 'Slackware to jedna z najstarszych, wciąż aktywnie rozwijanych dystrybucji Linuksa, znana ze swojego przywiązania do tradycji uniksowej, prostoty i stabilności. Nie posiada skomplikowanych narzędzi konfiguracyjnych, preferując ręczną edycję plików tekstowych. Jest skierowana do doświadczonych użytkowników, którzy chcą dogłębnie poznać i kontrolować swój system. Używa tradycyjnego systemu zarządzania pakietami (pkgtool, installpkg, removepkg).', NULL, 'img/slackware.png', 'http://www.slackware.com/', 1, '2025-04-16 08:57:44'),
(35, 'Void Linux', 'Void Linux to niezależna dystrybucja Linuksa typu rolling-release, budowana od podstaw. Wyróżnia się użyciem własnego menedżera pakietów XBPS (X Binary Package System) oraz systemu inicjalizacji runit zamiast bardziej powszechnego systemd. Stawia na prostotę, stabilność i wydajność. Oferuje zarówno wersje z glibc, jak i musl libc. Jest przeznaczona dla użytkowników ceniących sobie alternatywne podejście do zarządzania systemem.', NULL, 'img/void_linux.png', 'https://voidlinux.org/', 1, '2025-04-16 08:58:19'),
(36, 'Qubes OS', 'Qubes OS to dystrybucja Linuksa skupiona na bezpieczeństwie poprzez izolację (security by compartmentalization). Wykorzystuje wirtualizację (Xen hypervisor) do uruchamiania różnych części systemu i aplikacji w oddzielnych, odizolowanych maszynach wirtualnych (Qubes). Pozwala to na segregację zadań (np. praca, bankowość, przeglądanie internetu) i ogranicza skutki ewentualnego ataku na jedną z części systemu. Jest to system dla zaawansowanych użytkowników świadomych kwestii bezpieczeństwa.', NULL, 'img/qubes_os.png', 'https://www.qubes-os.org/', 1, '2025-04-16 09:06:57'),
(37, 'Proxmox Virtual Environment (Proxmox VE)', 'Proxmox VE to otwartoźródłowa platforma do wirtualizacji serwerów, bazująca na Debianie. Integruje wirtualizację opartą na KVM (dla maszyn wirtualnych) oraz konteneryzację LXC, zarządzane przez wygodny interfejs webowy. Jest popularnym rozwiązaniem do budowy i zarządzania infrastrukturą wirtualną w firmach i domowych laboratoriach.', NULL, 'img/proxmox_virtual_environment__proxmox_ve_.png', 'https://www.proxmox.com/en/products/proxmox-virtual-environment/overview', 1, '2025-04-16 10:01:24'),
(38, 'FreeBSD', 'FreeBSD to nie jest dystrybucja Linuksa, lecz kompletny, zaawansowany system operacyjny typu Unix, wywodzący się z Berkeley Software Distribution (BSD). Jest znany ze swojej stabilności, wydajności, rozbudowanych funkcji sieciowych i bezpieczeństwa. Używa własnego jądra (kernel) i posiada unikalne cechy, jak np. doskonałe wsparcie dla systemu plików ZFS czy mechanizm \"jails\" do izolacji procesów. Jest często wykorzystywany w serwerach, systemach wbudowanych i jako podstawa dla innych systemów (np. macOS, TrueNAS CORE).', NULL, 'img/freebsd.png', 'https://www.freebsd.org/', 1, '2025-04-16 10:03:12'),
(39, 'OpenWrt', 'OpenWrt to system operacyjny oparty na jądrze Linuksa, przeznaczony głównie dla urządzeń wbudowanych, a zwłaszcza routerów sieciowych. Pozwala zastąpić oryginalne oprogramowanie producenta routera, oferując znacznie większą kontrolę, elastyczność i możliwości konfiguracji. Umożliwia instalację dodatkowych pakietów i dostosowanie routera do specyficznych potrzeb.', NULL, 'img/openwrt.png', 'https://openwrt.org/', 1, '2025-04-16 10:03:38'),
(40, 'TrueNAS SCALE', 'Opis (PL): TrueNAS SCALE to otwartoźródłowy system operacyjny do budowy sieciowych pamięci masowych (NAS), bazujący na Debianie Linux (w odróżnieniu od TrueNAS CORE, który bazuje na FreeBSD). Kładzie silny nacisk na niezawodność przechowywania danych dzięki wykorzystaniu systemu plików ZFS. Oferuje również wsparcie dla wirtualizacji (KVM) i aplikacji kontenerowych (Docker/Kubernetes), tworząc platformę hiperkonwergentną.', NULL, 'img/truenas_scale.png', 'https://www.truenas.com/truenas-scale/', 1, '2025-04-16 10:04:14'),
(41, 'OPNsense', 'OPNsense to otwartoźródłowa, łatwa w użyciu platforma firewall i router, bazująca na FreeBSD (nie jest to dystrybucja Linuksa). Powstała jako fork pfSense. Oferuje bogaty zestaw funkcji bezpieczeństwa, regularne aktualizacje i nowoczesny interfejs użytkownika zarządzany przez przeglądarkę. Jest popularnym wyborem do zabezpieczania sieci w domach i firmach.', NULL, 'img/opnsense.png', 'https://opnsense.org/', 1, '2025-04-16 10:05:44'),
(42, 'pfSense', 'pfSense to również otwartoźródłowa platforma firewall i router, bazująca na FreeBSD (nie jest to dystrybucja Linuksa). Jest to bardzo dojrzały i powszechnie stosowany projekt, znany ze swojej stabilności, elastyczności i ogromnej liczby dostępnych funkcji oraz pakietów rozszerzeń. Podobnie jak OPNsense, jest zarządzany przez interfejs webowy i służy do budowy zaawansowanych rozwiązań sieciowych.', NULL, 'img/pfsense.png', 'https://www.pfsense.org/', 1, '2025-04-16 10:07:06'),
(43, 'OpenBSD', 'OpenBSD to kolejny system operacyjny typu Unix z rodziny BSD (podobnie jak FreeBSD, nie jest to Linux). Jest znany przede wszystkim z legendarnego nacisku na bezpieczeństwo, poprawność kodu i zintegrowaną kryptografię. Projekt OpenBSD jest również źródłem wielu powszechnie używanych narzędzi bezpieczeństwa, takich jak OpenSSH, LibreSSL czy pf (Packet Filter firewall, używany też w pfSense/OPNsense). Jest to system dla bardzo świadomych użytkowników, często używany jako bezpieczny serwer lub firewall.', NULL, 'img/openbsd.png', 'https://www.openbsd.org/', 1, '2025-04-16 10:07:46'),
(44, 'Raspberry Pi OS (wcześniej Raspbian)', 'Raspberry Pi OS to oficjalny system operacyjny dla popularnych minikomputerów Raspberry Pi. Jest to dystrybucja bazująca na Debianie, specjalnie zoptymalizowana pod kątem sprzętu Raspberry Pi. Oferuje kompletne środowisko graficzne (oparte na LXDE/PIXEL) oraz dostęp do szerokiej gamy oprogramowania, idealne do nauki programowania, projektów elektronicznych i zastosowań multimedialnych.', NULL, 'img/raspberry_pi_os__wcze__niej_raspbian_.png', 'https://www.raspberrypi.com/software/', 1, '2025-04-16 10:09:24'),
(45, 'Android', 'Android to system operacyjny bazujący na jądrze Linuksa, rozwijany przez Google, przeznaczony głównie dla urządzeń mobilnych (smartfony, tablety). Chociaż używa jądra Linuksa, jego warstwa użytkownika (biblioteki, środowisko uruchomieniowe ART/Dalvik) znacząco różni się od typowych dystrybucji GNU/Linux. Jest to najpopularniejszy system mobilny na świecie, z dostępem do milionów aplikacji przez sklep Google Play.', NULL, 'img/android.png', 'https://www.android.com/', 1, '2025-04-16 10:10:38'),
(46, 'Chrome OS / ChromeOS Flex', 'Chrome OS to system operacyjny bazujący na jądrze Linuksa, rozwijany przez Google, zaprojektowany głównie do pracy z aplikacjami webowymi i usługami Google. Jest domyślnie instalowany na urządzeniach Chromebook. Charakteryzuje się szybkością, prostotą i bezpieczeństwem. ChromeOS Flex to wersja, którą można zainstalować na standardowych komputerach PC i Mac, aby dać im drugie życie jako urządzenia podobne do Chromebooków.', NULL, 'img/chrome_os___chromeos_flex.png', 'https://chromeenterprise.google/os/chromeosflex/', 1, '2025-04-16 10:11:10'),
(47, 'Clear Linux OS', 'Clear Linux OS to dystrybucja Linuksa tworzona i optymalizowana przez firmę Intel pod kątem wydajności, bezpieczeństwa i skalowalności, szczególnie na platformach Intel. Jest często wykorzystywana w zastosowaniach chmurowych, kontenerowych i AI/ML. Wyróżnia się agresywnymi optymalizacjami kompilatora, systemem aktualizacji bezstanowych (stateless) i podziałem oprogramowania na \"bundle\".', NULL, 'img/clear_linux_os.png', 'https://clearlinux.org/', 1, '2025-04-16 10:12:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `distributions`
--
ALTER TABLE `distributions`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
