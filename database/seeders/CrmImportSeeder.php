<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CrmImportSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // --- CLIENTS ---
        DB::unprepared(<<<'SQL'
INSERT INTO `clients` VALUES
(3,'Kalua Rodriguez','kaluaarauz@gmail.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 14:13:01'),
(4,'Jhoselin Alva','jhos_ale@hotmail.it',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 14:14:48'),
(5,'Pablo Pinxit','pablo-pinxit@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(6,'Ruth Sanchez','ruth-sanchez@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(7,'Phoebe Hoyt','phoebe@activenglish.it',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 16:05:37'),
(8,'Nicola Farioli','nicola-farioli@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(9,'Anthony Cueva','anthony-cueva@pendiente.dgzconsulting.com',NULL,NULL,0,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 14:22:39'),
(10,'Edith Rodriguez','edith-rodriguez@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(11,'JuanCarlos Osorio','juancarlos-osorio@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(12,'Andrea Arellano','andrea-arellano@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(13,'Estefanny Gutierrez','estefanny-gutierrez@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(14,'Edgar Aranibar','edgar-aranibar@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(15,'Daniel Sigua','daniel-sigua@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(16,'Jaime Linares','jaime-linares@pendiente.dgzconsulting.com',NULL,NULL,0,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 16:06:29'),
(17,'LingChengKun','lingchengkun@pendiente.dgzconsulting.com',NULL,NULL,0,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 14:22:34'),
(18,'Frank Del Valle','frank-del-valle@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(19,'Santiago Falcon','santiago-falcon@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(20,'NEIDA','neida@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(21,'Dayana Cuba','dayana-cuba@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(22,'Alvaro Santiago','alvaro-santiago@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(23,'HildeSportel','hildesportel@pendiente.dgzconsulting.com',NULL,NULL,0,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 14:22:30'),
(24,'Ana Orero','info@sabiduriadealma.com',NULL,NULL,1,'$2y$12$//kJanT.0qM6EA8w0gutYO1Yqd459o4i73B4yfam5gfL1NAqAASRy',NULL,NULL,'2026-06-15 13:35:55','2026-06-15 21:51:12'),
(25,'Vincenzo','vincenzo@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(26,'Stanlly Villegas','stanlly-villegas@pendiente.dgzconsulting.com',NULL,NULL,0,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 14:22:27'),
(27,'SINDACATOASE','sindacatoase@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(28,'Pavel Palao','pavel-palao@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(29,'Oscar Herrera','oscar-herrera@pendiente.dgzconsulting.com',NULL,NULL,0,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 14:22:23'),
(30,'Fredy Gomez','fredy-gomez@pendiente.dgzconsulting.com',NULL,NULL,0,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 14:22:22'),
(31,'Roberta Andreoni','roberta-andreoni@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(32,'Yelines Salazar','yelines-salazar@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(33,'Jose Antonio','jose-antonio@pendiente.dgzconsulting.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(34,'Lilia','lilia@pendiente.dgzconsulting.com',NULL,NULL,0,NULL,NULL,NULL,'2026-06-15 13:35:55','2026-06-15 14:22:17'),
(35,'Joel Carbajal','joelcarbajal@gmail.com',NULL,NULL,1,NULL,NULL,NULL,'2026-06-15 15:56:12','2026-06-15 15:56:12');
SQL);

        // --- SITES (username/password omitted - encrypted with old APP_KEY, must re-enter) ---
        DB::unprepared(<<<'SQL'
INSERT INTO `sites` (`id`,`client_id`,`name`,`domain`,`slug`,`login_url`,`username`,`password`,`platform`,`host`,`has_blog`,`notes`,`created_at`,`updated_at`) VALUES
(1,3,'Kaluaarauz','kaluaarauz.com',NULL,'https://kaluaarauz.com/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(2,4,'JhoselinsWorld','jhoselinsworld.com',NULL,'https://jhoselinsworld.com/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(3,5,'PabloPinxit','pablopinxit.com',NULL,'https://pablopinxit.com/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(4,6,'SoulHealingSpace','soulhealingspace.com',NULL,'https://soulhealingspace.com/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(5,7,'ActivenglishAcademy','activenglish.it',NULL,'https://activenglish.it/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 16:05:37'),
(6,7,'ActivenglishAcademyEN','activenglishacademy.co.uk',NULL,'https://activenglishacademy.co.uk/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(7,8,'NicolaFarioli','nicolafarioli.com',NULL,'https://nicolafarioli.com/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(8,8,'NicolaFarioliIT','nicolafarioli.it',NULL,'https://nicolafarioli.it/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(9,8,'LaGrottaDelCristallo','lagrotta-del-cristallo.it',NULL,'https://lagrotta-del-cristallo.it/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(10,8,'NicolaFarioliShop','shop.nicolafarioli.com',NULL,'https://shop.nicolafarioli.com/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(11,8,'LaGrottaDelCristalloShop','shop.lagrotta-del-cristallo.it',NULL,'https://shop.lagrotta-del-cristallo.it/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(12,9,'Creacos','creacos.it',NULL,'https://creacos.it/wp-login.phpwp-login.php',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(13,10,'ClubLibertadEuropa','clublibertadeuropa.eu',NULL,'https://clublibertadeuropa.eu/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(14,11,'ZVENIA','zvenia.com',NULL,'http://zvenia.com/wp-admin',NULL,NULL,'WordPress','ProfesionalHosting',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(15,12,'FenixService','fenixserviceagenzia.it',NULL,'https://fenixserviceagenzia.it/wp-login.php',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:44:38'),
(16,12,'DatabaseFenixService','database.fenixserviceagenzia.it',NULL,'https://database.fenixserviceagenzia.it/masking1515masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:44:38'),
(17,13,'RistoranteDonPeru','ristorantedonperu.it',NULL,'https://ristorantedonperu.it/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(18,14,'Cocalmayo','cocalmayo.pe',NULL,'https://cocalmayo.pe/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(19,15,'MEDIACOMGROUP','mediacomgroup.it',NULL,'http://mediacomgroup.it/masking1515',NULL,NULL,'WordPress',NULL,0,NULL,'2026-06-15 13:35:55','2026-06-15 13:44:38'),
(20,15,'ColombiaTierraQuerida','colombiatierraquerida.it',NULL,'http://mediacomgroup.it/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:44:38'),
(21,16,'GreenPortMarineService','greenportmarineservice.com',NULL,'https://greenportmarineservice.com/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(22,16,'GreenPortPanamaCanal','greenport-panamacanal.com',NULL,'https://greenport-panamacanal.com/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(23,17,'LingChengkun','linchengkun.com',NULL,'https://linchengkun.com/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(24,18,'Oxiponce','oxiponce.com',NULL,'https://oxiponce.com/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(25,18,'Transportes-AK','transportes-ak.com',NULL,'https://transportes-ak.com/wp-admin',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(26,19,'ViolinistParis','violinist-paris.com',NULL,'https://violinist-paris.com/',NULL,NULL,NULL,'SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(27,20,'Neida','neidamultiservizi.it',NULL,'http://neidamultiservizi.it/',NULL,NULL,NULL,NULL,0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(28,16,'AIDSMARITIMEREPORTS','aidsmaritimereports.com',NULL,'aidsmaritimereports.com/wp-admin',NULL,NULL,'WordPress',NULL,0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(29,21,'LasBendecidas','ingenieriasgin.com',NULL,'https://ingenieriasgin.com/lasbendecidas.com/wp-admin',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(30,22,'ChimiVuelos','chimivuelos.com',NULL,'https://ingenieriasgin.com/chimivuelos.com/masking1515',NULL,NULL,'WordPress',NULL,0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(31,23,'Hildesportel','hildesportel.com',NULL,'https://ingenieriasgin.com/lasbendecidas.com/wp-admin',NULL,NULL,'WordPress',NULL,0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(32,24,'EspacioSanarte','espaciosanarte.com',NULL,'https://ingenieriasgin.com/',NULL,NULL,NULL,NULL,0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(33,25,'V4BFILMS','v4bfilms.com',NULL,'https://v4bfilms.com/',NULL,NULL,NULL,NULL,0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(34,26,'ProntoInterventoMilano24H','prontointerventomilano24h.it',NULL,'https://prontointerventomilano24h.it/masking1515',NULL,NULL,'WordPress',NULL,0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(35,27,'SINDACATOASEU','sindacatoaseu.it',NULL,NULL,NULL,NULL,NULL,'Vercel',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(36,29,'OGRATTA','ogratta.com.pe',NULL,'https://ogratta.com.pe/masking1515',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(37,30,'GomezProductions','gomezproductions.it',NULL,'https://gomezproductions.it/masking1515',NULL,NULL,'WordPress',NULL,0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(38,31,'VillaEstRosa','villaestrosa.it',NULL,'https://villaestrosa.it/',NULL,NULL,NULL,'SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(39,14,'IngenieriaSGIN','cacaltourubamba.com',NULL,'https://ingenieriasgin.com/',NULL,NULL,NULL,NULL,0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(40,32,'Creacos','shiuamilano.it',NULL,'https://creacos.it/wp-login.phpwp-login.php',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(41,33,'Creacos','kioskolatino.com',NULL,'https://creacos.it/wp-login.phpwp-login.php',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(42,34,'Creacos','gymbeauty.it',NULL,'https://creacos.it/wp-login.phpwp-login.php',NULL,NULL,'WordPress','SiteGround',0,NULL,'2026-06-15 13:35:55','2026-06-15 13:35:55'),
(43,24,'SabiduriaDeAlma (.es)','sabiduriadealma.es',NULL,NULL,NULL,NULL,'WordPress',NULL,0,NULL,'2026-06-15 14:48:18','2026-06-15 14:48:18'),
(44,24,'SabiduriaDeAlma (.com)','sabiduriadealma.com',NULL,NULL,NULL,NULL,'WordPress',NULL,0,NULL,'2026-06-15 14:48:18','2026-06-15 14:48:18'),
(45,24,'ArbolDeLaKabala','arboldelakabala.com',NULL,NULL,NULL,NULL,'WordPress',NULL,0,NULL,'2026-06-15 14:48:18','2026-06-15 14:48:18'),
(46,24,'ConciertosPabloNahual','conciertospablonahual.com',NULL,NULL,NULL,NULL,'WordPress',NULL,0,NULL,'2026-06-15 14:48:18','2026-06-15 14:48:18'),
(47,24,'ArbolDeVida','arboldevida.es',NULL,NULL,NULL,NULL,'WordPress',NULL,0,NULL,'2026-06-15 14:48:18','2026-06-15 14:48:18'),
(48,24,'PabloNahual','pablonahual.com',NULL,NULL,NULL,NULL,'WordPress',NULL,0,NULL,'2026-06-15 14:48:18','2026-06-15 14:48:18'),
(49,24,'ModeloOctatrico','modelooctatrico.com','modelo-octatrico',NULL,NULL,NULL,'Astro','Vercel',1,'Repo (privado, solo admin - el cliente no tiene acceso): https://github.com/DGZ-Consulting/modelo-octatrico\nBlog actualmente servido desde Strapi (cms.dgzconsulting.com), pendiente migrar al modulo Post del CRM (ver Fase 3).','2026-06-15 14:48:18','2026-06-15 15:56:12'),
(50,35,'ConkretPeru','conkretperu.com','conkretperu',NULL,NULL,NULL,'Astro','Vercel',1,'Repo: /Users/mirkodgz/Projects/joel-peru/conkret-peru-sito','2026-06-15 15:56:12','2026-06-15 15:56:12');
SQL);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('Clients (33) and Sites (47) imported. Run CrmPostsSeeder next for posts.');
    }
}
