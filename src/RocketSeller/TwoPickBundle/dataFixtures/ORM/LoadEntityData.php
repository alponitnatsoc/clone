<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Entity;
use RocketSeller\TwoPickBundle\Entity\EntityType;

class LoadEntityData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Add all the entities to the database
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {

        /* Type eps */

        $EntityEpsFosyga = new Entity();
        $EntityEpsFosyga->setName('EPS FOSYGA');
        $EntityEpsFosyga->setPayrollCode('5');
        $EntityEpsFosyga->setPilaCode('MIN001');
        $EntityEpsFosyga->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsFosyga);

        $EntityEpsCafesaludLiqSaludcop = new Entity();
        $EntityEpsCafesaludLiqSaludcop->setName('EPS CAFESALUD - LIQ. SALUDCOP');
        $EntityEpsCafesaludLiqSaludcop->setPayrollCode('10');
        $EntityEpsCafesaludLiqSaludcop->setPilaCode('EPS003');
        $EntityEpsCafesaludLiqSaludcop->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsCafesaludLiqSaludcop);

        $EntityEpsCapresoca = new Entity();
        $EntityEpsCapresoca->setName('EPS CAPRESOCA');
        $EntityEpsCapresoca->setPayrollCode('18');
        $EntityEpsCapresoca->setPilaCode('EPS025');
        $EntityEpsCapresoca->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsCapresoca);

        $EntityEpsComfenalcoValle = new Entity();
        $EntityEpsComfenalcoValle->setName('EPS COMFENALCO VALLE ');
        $EntityEpsComfenalcoValle->setPayrollCode('40');
        $EntityEpsComfenalcoValle->setPilaCode('EPS012');
        $EntityEpsComfenalcoValle->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsComfenalcoValle);

        $EntityEpsCompensar = new Entity();
        $EntityEpsCompensar->setName('EPS COMPENSAR');
        $EntityEpsCompensar->setPayrollCode('50');
        $EntityEpsCompensar->setPilaCode('EPS008 ');
        $EntityEpsCompensar->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsCompensar);

        $EntityEpsCoomeva = new Entity();
        $EntityEpsCoomeva->setName('EPS COOMEVA');
        $EntityEpsCoomeva->setPayrollCode('70');
        $EntityEpsCoomeva->setPilaCode('EPS016');
        $EntityEpsCoomeva->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsCoomeva);

        $EntityEpsCruzblanca = new Entity();
        $EntityEpsCruzblanca->setName('EPS CRUZBLANCA');
        $EntityEpsCruzblanca->setPayrollCode('80');
        $EntityEpsCruzblanca->setPilaCode('EPS023');
        $EntityEpsCruzblanca->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsCruzblanca);

        $EntityEpsFamisanar = new Entity();
        $EntityEpsFamisanar->setName('EPS FAMISANAR');
        $EntityEpsFamisanar->setPayrollCode('90');
        $EntityEpsFamisanar->setPilaCode('EPS017');
        $EntityEpsFamisanar->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsFamisanar);

        $EntityEpsNuevaEps = new Entity();
        $EntityEpsNuevaEps->setName('EPS NUEVA EPS ');
        $EntityEpsNuevaEps->setPayrollCode('101');
        $EntityEpsNuevaEps->setPilaCode('EPS037');
        $EntityEpsNuevaEps->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsNuevaEps);

        $EntityEpsSaludColpatria = new Entity();
        $EntityEpsSaludColpatria->setName('EPS SALUD COLPATRIA');
        $EntityEpsSaludColpatria->setPayrollCode('120');
        $EntityEpsSaludColpatria->setPilaCode('EPS015');
        $EntityEpsSaludColpatria->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsSaludColpatria);

        $EntityEpsSaludtototal = new Entity();
        $EntityEpsSaludtototal->setName('EPS SALUDTOTOTAL');
        $EntityEpsSaludtototal->setPayrollCode('130');
        $EntityEpsSaludtototal->setPilaCode('EPS002');
        $EntityEpsSaludtototal->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsSaludtototal);

        $EntityEpsSaludvida = new Entity();
        $EntityEpsSaludvida->setName('EPS SALUDVIDA');
        $EntityEpsSaludvida->setPayrollCode('144');
        $EntityEpsSaludvida->setPilaCode('EPS033');
        $EntityEpsSaludvida->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsSaludvida);

        $EntityEpsSanitas = new Entity();
        $EntityEpsSanitas->setName('EPS SANITAS');
        $EntityEpsSanitas->setPayrollCode('150');
        $EntityEpsSanitas->setPilaCode('EPS005');
        $EntityEpsSanitas->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsSanitas);

        $EntityEpsSOSServicioOccidentalDeSalud = new Entity();
        $EntityEpsSOSServicioOccidentalDeSalud->setName('EPS S.O.S - SERVICIO OCCIDENTAL DE SALUD');
        $EntityEpsSOSServicioOccidentalDeSalud->setPayrollCode('160');
        $EntityEpsSOSServicioOccidentalDeSalud->setPilaCode('EPS018');
        $EntityEpsSOSServicioOccidentalDeSalud->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsSOSServicioOccidentalDeSalud);

        $EntityEpsSura = new Entity();
        $EntityEpsSura->setName('EPS SURA');
        $EntityEpsSura->setPayrollCode('170');
        $EntityEpsSura->setPilaCode('EPS010');
        $EntityEpsSura->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsSura);

        $EntityEpsEcoopsosEntidadCooperativaSolidariaDeSalud = new Entity();
        $EntityEpsEcoopsosEntidadCooperativaSolidariaDeSalud->setName('EPS ECOOPSOS - ENTIDAD COOPERATIVA SOLIDARIA DE SALUD');
        $EntityEpsEcoopsosEntidadCooperativaSolidariaDeSalud->setPayrollCode('190');
        $EntityEpsEcoopsosEntidadCooperativaSolidariaDeSalud->setPilaCode('ESSC91');
        $EntityEpsEcoopsosEntidadCooperativaSolidariaDeSalud->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsEcoopsosEntidadCooperativaSolidariaDeSalud);

        $EntityEpsMutualSer = new Entity();
        $EntityEpsMutualSer->setName('EPS MUTUAL SER ');
        $EntityEpsMutualSer->setPayrollCode('195');
        $EntityEpsMutualSer->setPilaCode('ESSC07');
        $EntityEpsMutualSer->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsMutualSer);

        $EntityEpsCafam = new Entity();
        $EntityEpsCafam->setName('EPS CAFAM');
        $EntityEpsCafam->setPayrollCode('200');
        $EntityEpsCafam->setPilaCode('CCFC18');
        $EntityEpsCafam->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsCafam);

        $EntityEpsAmbuq = new Entity();
        $EntityEpsAmbuq->setName('EPS AMBUQ');
        $EntityEpsAmbuq->setPayrollCode('205');
        $EntityEpsAmbuq->setPilaCode('ESSC76');
        $EntityEpsAmbuq->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsAmbuq);

        $EntityEpsComfanarino = new Entity();
        $EntityEpsComfanarino->setName('EPS COMFANARIÑO ');
        $EntityEpsComfanarino->setPayrollCode('210');
        $EntityEpsComfanarino->setPilaCode('CFFC27');
        $EntityEpsComfanarino->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsComfanarino);

        $EntityEpsMallamas = new Entity();
        $EntityEpsMallamas->setName('EPS MALLAMAS');
        $EntityEpsMallamas->setPayrollCode('215');
        $EntityEpsMallamas->setPilaCode('EPSIC5');
        $EntityEpsMallamas->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsMallamas);

        $EntityEpsPijaosalud = new Entity();
        $EntityEpsPijaosalud->setName('EPS PIJAOSALUD');
        $EntityEpsPijaosalud->setPayrollCode('220');
        $EntityEpsPijaosalud->setPilaCode('EPSIC6');
        $EntityEpsPijaosalud->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsPijaosalud);

        $EntityEpsAsmetSalud = new Entity();
        $EntityEpsAsmetSalud->setName('EPS ASMET SALUD');
        $EntityEpsAsmetSalud->setPayrollCode('225');
        $EntityEpsAsmetSalud->setPilaCode('ESSC62');
        $EntityEpsAsmetSalud->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsAsmetSalud);

        $EntityEpsComfamiliarHuila = new Entity();
        $EntityEpsComfamiliarHuila->setName('EPS COMFAMILIAR HUILA');
        $EntityEpsComfamiliarHuila->setPayrollCode('230');
        $EntityEpsComfamiliarHuila->setPilaCode('CFFC24');
        $EntityEpsComfamiliarHuila->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsComfamiliarHuila);

        $EntityEpsComfacor = new Entity();
        $EntityEpsComfacor->setName('EPS COMFACOR');
        $EntityEpsComfacor->setPayrollCode('235');
        $EntityEpsComfacor->setPilaCode('CCFC15');
        $EntityEpsComfacor->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsComfacor);

        $EntityEpsCajacopi = new Entity();
        $EntityEpsCajacopi->setName('EPS CAJACOPI');
        $EntityEpsCajacopi->setPayrollCode('240');
        $EntityEpsCajacopi->setPilaCode('CFFC55');
        $EntityEpsCajacopi->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsCajacopi);

        $EntityEpsConvida = new Entity();
        $EntityEpsConvida->setName('EPS CONVIDA');
        $EntityEpsConvida->setPayrollCode('245');
        $EntityEpsConvida->setPilaCode('EPSC22');
        $EntityEpsConvida->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsConvida);

        $EntityEpsSaviaSalud = new Entity();
        $EntityEpsSaviaSalud->setName('EPS SAVIA SALUD ');
        $EntityEpsSaviaSalud->setPayrollCode('250');
        $EntityEpsSaviaSalud->setPilaCode('CCFC02');
        $EntityEpsSaviaSalud->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsSaviaSalud);

        $EntityEpsEmssanar = new Entity();
        $EntityEpsEmssanar->setName('EPS EMSSANAR');
        $EntityEpsEmssanar->setPayrollCode('260');
        $EntityEpsEmssanar->setPilaCode('ESSC18');
        $EntityEpsEmssanar->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsEmssanar);

        $EntityEpsCoosalud = new Entity();
        $EntityEpsCoosalud->setName('EPS COOSALUD');
        $EntityEpsCoosalud->setPayrollCode('265');
        $EntityEpsCoosalud->setPilaCode('ESSC24');
        $EntityEpsCoosalud->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsCoosalud);

        $EntityEpsColsubsidio = new Entity();
        $EntityEpsColsubsidio->setName('EPS COLSUBSIDIO');
        $EntityEpsColsubsidio->setPayrollCode('270');
        $EntityEpsColsubsidio->setPilaCode('CCFC10');
        $EntityEpsColsubsidio->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsColsubsidio);

        $EntityEpsComfacundi = new Entity();
        $EntityEpsComfacundi->setName('EPS COMFACUNDI');
        $EntityEpsComfacundi->setPayrollCode('275');
        $EntityEpsComfacundi->setPilaCode('CFFC53');
        $EntityEpsComfacundi->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsComfacundi);

        $EntityEpsCapitalSalud = new Entity();
        $EntityEpsCapitalSalud->setName('EPS CAPITAL SALUD ');
        $EntityEpsCapitalSalud->setPayrollCode('285');
        $EntityEpsCapitalSalud->setPilaCode('EPSC34');
        $EntityEpsCapitalSalud->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsCapitalSalud);

        $EntityEpsManexka = new Entity();
        $EntityEpsManexka->setName('EPS MANEXKA');
        $EntityEpsManexka->setPayrollCode('290');
        $EntityEpsManexka->setPilaCode('EPSIC2');
        $EntityEpsManexka->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsManexka);

        $EntityEpsEmdisalud = new Entity();
        $EntityEpsEmdisalud->setName('EPS EMDISALUD');
        $EntityEpsEmdisalud->setPayrollCode('295');
        $EntityEpsEmdisalud->setPilaCode('ESSC02');
        $EntityEpsEmdisalud->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsEmdisalud);

        $EntityEpsAliansalud = new Entity();
        $EntityEpsAliansalud->setName('EPS ALIANSALUD ');
        $EntityEpsAliansalud->setPayrollCode('121');
        $EntityEpsAliansalud->setPilaCode('EPS001');
        $EntityEpsAliansalud->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsAliansalud);

        $EntityEpsComfachoco = new Entity();
        $EntityEpsComfachoco->setName('EPS COMFACHOCO');
        $EntityEpsComfachoco->setPayrollCode('209');
        $EntityEpsComfachoco->setPilaCode('CCFC20');
        $EntityEpsComfachoco->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsComfachoco);

        $EntityEpsAnasWayuu = new Entity();
        $EntityEpsAnasWayuu->setName('EPS ANAS WAYUU');
        $EntityEpsAnasWayuu->setPayrollCode('219');
        $EntityEpsAnasWayuu->setPilaCode('EPSIC4');
        $EntityEpsAnasWayuu->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsAnasWayuu);

        $EntityEpsComparta = new Entity();
        $EntityEpsComparta->setName('EPS COMPARTA');
        $EntityEpsComparta->setPayrollCode('231');
        $EntityEpsComparta->setPilaCode('ESSC33');
        $EntityEpsComparta->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsComparta);

        /* Type arl */

        $EntityArpPositivaCompaniaDeSegurosInsitutoDeSegurosSociales = new Entity();
        $EntityArpPositivaCompaniaDeSegurosInsitutoDeSegurosSociales->setName('ARP POSITIVA COMPAÑÍA DE SEGUROS - INSITUTO DE SEGUROS SOCIALES');
        $EntityArpPositivaCompaniaDeSegurosInsitutoDeSegurosSociales->setPayrollCode('100');
        $EntityArpPositivaCompaniaDeSegurosInsitutoDeSegurosSociales->setPilaCode('14-23');
        $EntityArpPositivaCompaniaDeSegurosInsitutoDeSegurosSociales->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArpPositivaCompaniaDeSegurosInsitutoDeSegurosSociales);

        $EntityArpAlfa = new Entity();
        $EntityArpAlfa->setName('ARP ALFA');
        $EntityArpAlfa->setPayrollCode('600');
        $EntityArpAlfa->setPilaCode('14-17');
        $EntityArpAlfa->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArpAlfa);

        $EntityArpSegurosDeVidaAurora = new Entity();
        $EntityArpSegurosDeVidaAurora->setName('ARP SEGUROS DE VIDA AURORA');
        $EntityArpSegurosDeVidaAurora->setPayrollCode('615');
        $EntityArpSegurosDeVidaAurora->setPilaCode('14-8');
        $EntityArpSegurosDeVidaAurora->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArpSegurosDeVidaAurora);

        $EntityArpSegurosBolivar = new Entity();
        $EntityArpSegurosBolivar->setName('ARP SEGUROS BOLIVAR');
        $EntityArpSegurosBolivar->setPayrollCode('625');
        $EntityArpSegurosBolivar->setPilaCode('14-7');
        $EntityArpSegurosBolivar->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArpSegurosBolivar);

        $EntityArpColmena = new Entity();
        $EntityArpColmena->setName('ARP COLMENA');
        $EntityArpColmena->setPayrollCode('630');
        $EntityArpColmena->setPilaCode('14-25');
        $EntityArpColmena->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArpColmena);

        $EntityArpColpatria = new Entity();
        $EntityArpColpatria->setName('ARP COLPATRIA');
        $EntityArpColpatria->setPayrollCode('635');
        $EntityArpColpatria->setPilaCode('14-4');
        $EntityArpColpatria->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArpColpatria);

        $EntityArpLaEquidad = new Entity();
        $EntityArpLaEquidad->setName('ARP LA EQUIDAD');
        $EntityArpLaEquidad->setPayrollCode('645');
        $EntityArpLaEquidad->setPilaCode('14-29');
        $EntityArpLaEquidad->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArpLaEquidad);

        $EntityArpLibertySeguros = new Entity();
        $EntityArpLibertySeguros->setName('ARP LIBERTY SEGUROS ');
        $EntityArpLibertySeguros->setPayrollCode('655');
        $EntityArpLibertySeguros->setPilaCode('14-18');
        $EntityArpLibertySeguros->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArpLibertySeguros);

        $EntityArpSuraSuratep = new Entity();
        $EntityArpSuraSuratep->setName('ARP SURA - SURATEP');
        $EntityArpSuraSuratep->setPayrollCode('670');
        $EntityArpSuraSuratep->setPilaCode('14-28');
        $EntityArpSuraSuratep->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArpSuraSuratep);

        $EntityArpMapfre = new Entity();
        $EntityArpMapfre->setName('ARP MAPFRE');
        $EntityArpMapfre->setPayrollCode('');
        $EntityArpMapfre->setPilaCode('14-30');
        $EntityArpMapfre->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArpMapfre);

        /* Type pensiones */

        $EntityAfpNoAporta = new Entity();
        $EntityAfpNoAporta->setName('NO APORTA');
        $EntityAfpNoAporta->setPayrollCode('0');
        $EntityAfpNoAporta->setPilaCode('0');
        $EntityAfpNoAporta->setEntityTypeEntityType($this->getReference('entityType-pensiones'));


        $manager->persist($EntityAfpNoAporta);

        $EntityAfpColpensiones = new Entity();
        $EntityAfpColpensiones->setName('AFP COLPENSIONES');
        $EntityAfpColpensiones->setPayrollCode('102');
        $EntityAfpColpensiones->setPilaCode('25-14');
        $EntityAfpColpensiones->setEntityTypeEntityType($this->getReference('entityType-pensiones'));


        $manager->persist($EntityAfpColpensiones);

        $EntityAfpPorvenirHorizonte = new Entity();
        $EntityAfpPorvenirHorizonte->setName('AFP PORVENIR - HORIZONTE');
        $EntityAfpPorvenirHorizonte->setPayrollCode('300');
        $EntityAfpPorvenirHorizonte->setPilaCode('230301');
        $EntityAfpPorvenirHorizonte->setEntityTypeEntityType($this->getReference('entityType-pensiones'));


        $manager->persist($EntityAfpPorvenirHorizonte);

        $EntityAfpColfondos = new Entity();
        $EntityAfpColfondos->setName('AFP COLFONDOS ');
        $EntityAfpColfondos->setPayrollCode('310');
        $EntityAfpColfondos->setPilaCode('231001');
        $EntityAfpColfondos->setEntityTypeEntityType($this->getReference('entityType-pensiones'));


        $manager->persist($EntityAfpColfondos);

        $EntityAfpProteccionIng = new Entity();
        $EntityAfpProteccionIng->setName('AFP PROTECCION - ING');
        $EntityAfpProteccionIng->setPayrollCode('330');
        $EntityAfpProteccionIng->setPilaCode('230201');
        $EntityAfpProteccionIng->setEntityTypeEntityType($this->getReference('entityType-pensiones'));


        $manager->persist($EntityAfpProteccionIng);

        $EntityAfpSkandiaOldMutualFondoDePensionesObligatorias = new Entity();
        $EntityAfpSkandiaOldMutualFondoDePensionesObligatorias->setName('AFP SKANDIA - OLD MUTUAL FONDO DE PENSIONES OBLIGATORIAS');
        $EntityAfpSkandiaOldMutualFondoDePensionesObligatorias->setPayrollCode('350');
        $EntityAfpSkandiaOldMutualFondoDePensionesObligatorias->setPilaCode('230901');
        $EntityAfpSkandiaOldMutualFondoDePensionesObligatorias->setEntityTypeEntityType($this->getReference('entityType-pensiones'));


        $manager->persist($EntityAfpSkandiaOldMutualFondoDePensionesObligatorias);

        /* Type fces */

        $EntityFcesPorvenir = new Entity();
        $EntityFcesPorvenir->setName('FCES PORVENIR');
        $EntityFcesPorvenir->setPayrollCode('400');
        $EntityFcesPorvenir->setPilaCode('03');
        $EntityFcesPorvenir->setEntityTypeEntityType($this->getReference('entityType-fces'));


        $manager->persist($EntityFcesPorvenir);

        $EntityFcesColfondos = new Entity();
        $EntityFcesColfondos->setName('FCES COLFONDOS ');
        $EntityFcesColfondos->setPayrollCode('410');
        $EntityFcesColfondos->setPilaCode('10');
        $EntityFcesColfondos->setEntityTypeEntityType($this->getReference('entityType-fces'));


        $manager->persist($EntityFcesColfondos);

        $EntityFcesHorizonte = new Entity();
        $EntityFcesHorizonte->setName('FCES HORIZONTE');
        $EntityFcesHorizonte->setPayrollCode('420');
        $EntityFcesHorizonte->setPilaCode('05');
        $EntityFcesHorizonte->setEntityTypeEntityType($this->getReference('entityType-fces'));


        $manager->persist($EntityFcesHorizonte);

        $EntityFcesProteccion = new Entity();
        $EntityFcesProteccion->setName('FCES PROTECCION');
        $EntityFcesProteccion->setPayrollCode('430');
        $EntityFcesProteccion->setPilaCode('02');
        $EntityFcesProteccion->setEntityTypeEntityType($this->getReference('entityType-fces'));


        $manager->persist($EntityFcesProteccion);

        $EntityFcesSkandia = new Entity();
        $EntityFcesSkandia->setName('FCES SKANDIA');
        $EntityFcesSkandia->setPayrollCode('450');
        $EntityFcesSkandia->setPilaCode('19');
        $EntityFcesSkandia->setEntityTypeEntityType($this->getReference('entityType-fces'));


        $manager->persist($EntityFcesSkandia);

        $EntityFcesFondoNacionalDelAhorro = new Entity();
        $EntityFcesFondoNacionalDelAhorro->setName('FCES FONDO NACIONAL DEL AHORRO');
        $EntityFcesFondoNacionalDelAhorro->setPayrollCode('460');
        $EntityFcesFondoNacionalDelAhorro->setPilaCode('');
        $EntityFcesFondoNacionalDelAhorro->setEntityTypeEntityType($this->getReference('entityType-fces'));


        $manager->persist($EntityFcesFondoNacionalDelAhorro);

        /* Type cajacomp */

        $EntityCcfColsubsidio = new Entity();
        $EntityCcfColsubsidio->setName('CCF COLSUBSIDIO');
        $EntityCcfColsubsidio->setPayrollCode('500');
        $EntityCcfColsubsidio->setPilaCode('CCF22');
        $EntityCcfColsubsidio->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfColsubsidio->addDepartment($this->getReference('c-code-343-d-code-'.'25'));
        $EntityCcfColsubsidio->addDepartment($this->getReference('c-code-343-d-code-'.'11'));

        $manager->persist($EntityCcfColsubsidio);

        $EntityCcfCafam = new Entity();
        $EntityCcfCafam->setName('CCF CAFAM');
        $EntityCcfCafam->setPayrollCode('507');
        $EntityCcfCafam->setPilaCode('CCF21');
        $EntityCcfCafam->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfCafam->addDepartment($this->getReference('c-code-343-d-code-'.'25'));
        $EntityCcfCafam->addDepartment($this->getReference('c-code-343-d-code-'.'11'));

        $manager->persist($EntityCcfCafam);

        $EntityCcfCafamaz = new Entity();
        $EntityCcfCafamaz->setName('CCF CAFAMAZ');
        $EntityCcfCafamaz->setPayrollCode('508');
        $EntityCcfCafamaz->setPilaCode('CFF65');
        $EntityCcfCafamaz->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfCafamaz->addDepartment($this->getReference('c-code-343-d-code-'.'91'));

        $manager->persist($EntityCcfCafamaz);

        $EntityCcfComfamiliarCamacol = new Entity();
        $EntityCcfComfamiliarCamacol->setName('CCF COMFAMILIAR CAMACOL');
        $EntityCcfComfamiliarCamacol->setPayrollCode('509');
        $EntityCcfComfamiliarCamacol->setPilaCode('CCF02');
        $EntityCcfComfamiliarCamacol->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfamiliarCamacol->addDepartment($this->getReference('c-code-343-d-code-'.'05'));

        $manager->persist($EntityCcfComfamiliarCamacol);

        $EntityCcfComfaoriente = new Entity();
        $EntityCcfComfaoriente->setName('CCF COMFAORIENTE ');
        $EntityCcfComfaoriente->setPayrollCode('510');
        $EntityCcfComfaoriente->setPilaCode('CCF36');
        $EntityCcfComfaoriente->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfaoriente->addDepartment($this->getReference('c-code-343-d-code-'.'54'));

        $manager->persist($EntityCcfComfaoriente);

        $EntityCcfComcaja = new Entity();
        $EntityCcfComcaja->setName('CCF COMCAJA');
        $EntityCcfComcaja->setPayrollCode('511');
        $EntityCcfComcaja->setPilaCode('CCF68');
        $EntityCcfComcaja->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComcaja->addDepartment($this->getReference('c-code-343-d-code-'.'94'));
        $EntityCcfComcaja->addDepartment($this->getReference('c-code-343-d-code-'.'95'));
        $EntityCcfComcaja->addDepartment($this->getReference('c-code-343-d-code-'.'97'));
        $EntityCcfComcaja->addDepartment($this->getReference('c-code-343-d-code-'.'99'));

        $manager->persist($EntityCcfComcaja);

        $EntityCcfComfachoco = new Entity();
        $EntityCcfComfachoco->setName('CCF COMFACHOCO ');
        $EntityCcfComfachoco->setPayrollCode('512');
        $EntityCcfComfachoco->setPilaCode('CCF29');
        $EntityCcfComfachoco->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfachoco->addDepartment($this->getReference('c-code-343-d-code-'.'27'));

        $manager->persist($EntityCcfComfachoco);

        $EntityCcfComfamiliarAtlantico = new Entity();
        $EntityCcfComfamiliarAtlantico->setName('CCF COMFAMILIAR ATLANTICO ');
        $EntityCcfComfamiliarAtlantico->setPayrollCode('513');
        $EntityCcfComfamiliarAtlantico->setPilaCode('CCF07');
        $EntityCcfComfamiliarAtlantico->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfamiliarAtlantico->addDepartment($this->getReference('c-code-343-d-code-'.'08'));

        $manager->persist($EntityCcfComfamiliarAtlantico);

        $EntityCcfComfamiliarLaDorada = new Entity();
        $EntityCcfComfamiliarLaDorada->setName('CCF COMFAMILIAR LA DORADA');
        $EntityCcfComfamiliarLaDorada->setPayrollCode('514');
        $EntityCcfComfamiliarLaDorada->setPilaCode('CCF12');
        $EntityCcfComfamiliarLaDorada->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfamiliarLaDorada->addDepartment($this->getReference('c-code-343-d-code-'.'17'));

        $manager->persist($EntityCcfComfamiliarLaDorada);

        $EntityCcfComfenalcoAntioquia = new Entity();
        $EntityCcfComfenalcoAntioquia->setName('CCF COMFENALCO ANTIOQUIA');
        $EntityCcfComfenalcoAntioquia->setPayrollCode('515');
        $EntityCcfComfenalcoAntioquia->setPilaCode('CCF03');
        $EntityCcfComfenalcoAntioquia->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfenalcoAntioquia->addDepartment($this->getReference('c-code-343-d-code-'.'05'));

        $manager->persist($EntityCcfComfenalcoAntioquia);

        $EntityCcfComfenalcoCartagena = new Entity();
        $EntityCcfComfenalcoCartagena->setName('CCF COMFENALCO CARTAGENA');
        $EntityCcfComfenalcoCartagena->setPayrollCode('516');
        $EntityCcfComfenalcoCartagena->setPilaCode('CCF08');
        $EntityCcfComfenalcoCartagena->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfenalcoCartagena->addDepartment($this->getReference('c-code-343-d-code-'.'13'));

        $manager->persist($EntityCcfComfenalcoCartagena);

        $EntityCcfComfenalcoValle = new Entity();
        $EntityCcfComfenalcoValle->setName('CCF COMFENALCO VALLE ');
        $EntityCcfComfenalcoValle->setPayrollCode('518');
        $EntityCcfComfenalcoValle->setPilaCode('CCF56');
        $EntityCcfComfenalcoValle->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfenalcoValle->addDepartment($this->getReference('c-code-343-d-code-'.'76'));

        $manager->persist($EntityCcfComfenalcoValle);

        $EntityCcfCajacopi = new Entity();
        $EntityCcfCajacopi->setName('CCF CAJACOPI');
        $EntityCcfCajacopi->setPayrollCode('520');
        $EntityCcfCajacopi->setPilaCode('CCF05');
        $EntityCcfCajacopi->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfCajacopi->addDepartment($this->getReference('c-code-343-d-code-'.'08'));

        $manager->persist($EntityCcfCajacopi);

        $EntityCcfComfenalcoSantander = new Entity();
        $EntityCcfComfenalcoSantander->setName('CCF COMFENALCO SANTANDER ');
        $EntityCcfComfenalcoSantander->setPayrollCode('522');
        $EntityCcfComfenalcoSantander->setPilaCode('CCF40');
        $EntityCcfComfenalcoSantander->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfenalcoSantander->addDepartment($this->getReference('c-code-343-d-code-'.'68'));

        $manager->persist($EntityCcfComfenalcoSantander);

        $EntityCcfComfenalcoTolima = new Entity();
        $EntityCcfComfenalcoTolima->setName('CCF COMFENALCO TOLIMA ');
        $EntityCcfComfenalcoTolima->setPayrollCode('524');
        $EntityCcfComfenalcoTolima->setPilaCode('CCF50');
        $EntityCcfComfenalcoTolima->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfenalcoTolima->addDepartment($this->getReference('c-code-343-d-code-'.'73'));

        $manager->persist($EntityCcfComfenalcoTolima);

        $EntityCcfComfiarArauca = new Entity();
        $EntityCcfComfiarArauca->setName('CCF COMFIAR ARAUCA');
        $EntityCcfComfiarArauca->setPayrollCode('526');
        $EntityCcfComfiarArauca->setPilaCode('CCF67');
        $EntityCcfComfiarArauca->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfiarArauca->addDepartment($this->getReference('c-code-343-d-code-'.'81'));

        $manager->persist($EntityCcfComfiarArauca);

        $EntityCcfCompensar = new Entity();
        $EntityCcfCompensar->setName('CCF COMPENSAR ');
        $EntityCcfCompensar->setPayrollCode('528');
        $EntityCcfCompensar->setPilaCode('CCF24');
        $EntityCcfCompensar->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfCompensar->addDepartment($this->getReference('c-code-343-d-code-'.'25'));
        $EntityCcfCompensar->addDepartment($this->getReference('c-code-343-d-code-'.'11'));

        $manager->persist($EntityCcfCompensar);

        $EntityCcfCajamag = new Entity();
        $EntityCcfCajamag->setName('CCF CAJAMAG');
        $EntityCcfCajamag->setPayrollCode('530');
        $EntityCcfCajamag->setPilaCode('CFF33');
        $EntityCcfCajamag->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfCajamag->addDepartment($this->getReference('c-code-343-d-code-'.'47'));

        $manager->persist($EntityCcfCajamag);

        $EntityCcfCafaba = new Entity();
        $EntityCcfCafaba->setName('CCF CAFABA');
        $EntityCcfCafaba->setPayrollCode('532');
        $EntityCcfCafaba->setPilaCode('CCF38');
        $EntityCcfCafaba->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfCafaba->addDepartment($this->getReference('c-code-343-d-code-'.'68'));

        $manager->persist($EntityCcfCafaba);

        $EntityCcfComfaboy = new Entity();
        $EntityCcfComfaboy->setName('CCF COMFABOY ');
        $EntityCcfComfaboy->setPayrollCode('534');
        $EntityCcfComfaboy->setPilaCode('CCF10');
        $EntityCcfComfaboy->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfaboy->addDepartment($this->getReference('c-code-343-d-code-'.'15'));

        $manager->persist($EntityCcfComfaboy);

        $EntityCcfComfamiliares = new Entity();
        $EntityCcfComfamiliares->setName('CCF COMFAMILIARES');
        $EntityCcfComfamiliares->setPayrollCode('538');
        $EntityCcfComfamiliares->setPilaCode('CCF11');
        $EntityCcfComfamiliares->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfamiliares->addDepartment($this->getReference('c-code-343-d-code-'.'17'));

        $manager->persist($EntityCcfComfamiliares);

        $EntityCcfComfamiliarCartagenaYBolivar = new Entity();
        $EntityCcfComfamiliarCartagenaYBolivar->setName('CCF COMFAMILIAR CARTAGENA Y BOLIVAR ');
        $EntityCcfComfamiliarCartagenaYBolivar->setPayrollCode('542');
        $EntityCcfComfamiliarCartagenaYBolivar->setPilaCode('CCF09');
        $EntityCcfComfamiliarCartagenaYBolivar->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfamiliarCartagenaYBolivar->addDepartment($this->getReference('c-code-343-d-code-'.'13'));

        $manager->persist($EntityCcfComfamiliarCartagenaYBolivar);

        $EntityCcfComfacor = new Entity();
        $EntityCcfComfacor->setName('CCF COMFACOR');
        $EntityCcfComfacor->setPayrollCode('546');
        $EntityCcfComfacor->setPilaCode('CCF16');
        $EntityCcfComfacor->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfacor->addDepartment($this->getReference('c-code-343-d-code-'.'23'));

        $manager->persist($EntityCcfComfacor);

        $EntityCcfComfacundi = new Entity();
        $EntityCcfComfacundi->setName('CCF COMFACUNDI ');
        $EntityCcfComfacundi->setPayrollCode('548');
        $EntityCcfComfacundi->setPilaCode('CCF26');
        $EntityCcfComfacundi->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfacundi->addDepartment($this->getReference('c-code-343-d-code-'.'25'));
        $EntityCcfComfacundi->addDepartment($this->getReference('c-code-343-d-code-'.'11'));

        $manager->persist($EntityCcfComfacundi);

        $EntityCcfComfenalcoQuindio = new Entity();
        $EntityCcfComfenalcoQuindio->setName('CCF COMFENALCO QUINDIO');
        $EntityCcfComfenalcoQuindio->setPayrollCode('549');
        $EntityCcfComfenalcoQuindio->setPilaCode('CFF43');
        $EntityCcfComfenalcoQuindio->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfenalcoQuindio->addDepartment($this->getReference('c-code-343-d-code-'.'63'));

        $manager->persist($EntityCcfComfenalcoQuindio);

        $EntityCcfComfandi = new Entity();
        $EntityCcfComfandi->setName('CCF COMFANDI ');
        $EntityCcfComfandi->setPayrollCode('550');
        $EntityCcfComfandi->setPilaCode('CCF57');
        $EntityCcfComfandi->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfandi->addDepartment($this->getReference('c-code-343-d-code-'.'76'));

        $manager->persist($EntityCcfComfandi);

        $EntityCcfComfaguajira = new Entity();
        $EntityCcfComfaguajira->setName('CCF COMFAGUAJIRA');
        $EntityCcfComfaguajira->setPayrollCode('556');
        $EntityCcfComfaguajira->setPilaCode('CFF30');
        $EntityCcfComfaguajira->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfaguajira->addDepartment($this->getReference('c-code-343-d-code-'.'44'));

        $manager->persist($EntityCcfComfaguajira);

        $EntityCcfComfamiliarRisaralda = new Entity();
        $EntityCcfComfamiliarRisaralda->setName('CCF COMFAMILIAR RISARALDA ');
        $EntityCcfComfamiliarRisaralda->setPayrollCode('558');
        $EntityCcfComfamiliarRisaralda->setPilaCode('CFF44');
        $EntityCcfComfamiliarRisaralda->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfamiliarRisaralda->addDepartment($this->getReference('c-code-343-d-code-'.'66'));

        $manager->persist($EntityCcfComfamiliarRisaralda);

        $EntityCcfCajaSai = new Entity();
        $EntityCcfCajaSai->setName('CCF CAJA SAI');
        $EntityCcfCajaSai->setPayrollCode('562');
        $EntityCcfCajaSai->setPilaCode('CFF64');
        $EntityCcfCajaSai->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfCajaSai->addDepartment($this->getReference('c-code-343-d-code-'.'88'));

        $manager->persist($EntityCcfCajaSai);

        $EntityCcfComfasucre = new Entity();
        $EntityCcfComfasucre->setName('CCF COMFASUCRE');
        $EntityCcfComfasucre->setPayrollCode('564');
        $EntityCcfComfasucre->setPilaCode('CCF41');
        $EntityCcfComfasucre->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfasucre->addDepartment($this->getReference('c-code-343-d-code-'.'70'));

        $manager->persist($EntityCcfComfasucre);

        $EntityCcfComfaca = new Entity();
        $EntityCcfComfaca->setName('CCF COMFACA');
        $EntityCcfComfaca->setPayrollCode('568');
        $EntityCcfComfaca->setPilaCode('CCF13');
        $EntityCcfComfaca->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfaca->addDepartment($this->getReference('c-code-343-d-code-'.'18'));

        $manager->persist($EntityCcfComfaca);

        $EntityCcfComfamiliarNarino = new Entity();
        $EntityCcfComfamiliarNarino->setName('CCF COMFAMILIAR NARIÑO ');
        $EntityCcfComfamiliarNarino->setPayrollCode('570');
        $EntityCcfComfamiliarNarino->setPilaCode('CFF35');
        $EntityCcfComfamiliarNarino->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfamiliarNarino->addDepartment($this->getReference('c-code-343-d-code-'.'52'));

        $manager->persist($EntityCcfComfamiliarNarino);

        $EntityCcfComfacauca = new Entity();
        $EntityCcfComfacauca->setName('CCF COMFACAUCA');
        $EntityCcfComfacauca->setPayrollCode('572');
        $EntityCcfComfacauca->setPilaCode('CCF14');
        $EntityCcfComfacauca->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfacauca->addDepartment($this->getReference('c-code-343-d-code-'.'19'));

        $manager->persist($EntityCcfComfacauca);

        $EntityCcfComfacesar = new Entity();
        $EntityCcfComfacesar->setName('CCF COMFACESAR');
        $EntityCcfComfacesar->setPayrollCode('574');
        $EntityCcfComfacesar->setPilaCode('CCF15');
        $EntityCcfComfacesar->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfacesar->addDepartment($this->getReference('c-code-343-d-code-'.'20'));

        $manager->persist($EntityCcfComfacesar);

        $EntityCcfComfamiliarHuila = new Entity();
        $EntityCcfComfamiliarHuila->setName('CCF COMFAMILIAR HUILA ');
        $EntityCcfComfamiliarHuila->setPayrollCode('576');
        $EntityCcfComfamiliarHuila->setPilaCode('CFF32');
        $EntityCcfComfamiliarHuila->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfamiliarHuila->addDepartment($this->getReference('c-code-343-d-code-'.'41'));

        $manager->persist($EntityCcfComfamiliarHuila);

        $EntityCcfCofrem = new Entity();
        $EntityCcfCofrem->setName('CCF COFREM');
        $EntityCcfCofrem->setPayrollCode('578');
        $EntityCcfCofrem->setPilaCode('CFF34');
        $EntityCcfCofrem->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfCofrem->addDepartment($this->getReference('c-code-343-d-code-'.'50'));

        $manager->persist($EntityCcfCofrem);

        $EntityCcfComfama = new Entity();
        $EntityCcfComfama->setName('CCF COMFAMA');
        $EntityCcfComfama->setPayrollCode('580');
        $EntityCcfComfama->setPilaCode('CCF04');
        $EntityCcfComfama->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfama->addDepartment($this->getReference('c-code-343-d-code-'.'05'));

        $manager->persist($EntityCcfComfama);

        $EntityCcfComfanorte = new Entity();
        $EntityCcfComfanorte->setName('CCF COMFANORTE ');
        $EntityCcfComfanorte->setPayrollCode('582');
        $EntityCcfComfanorte->setPilaCode('CFF37');
        $EntityCcfComfanorte->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfanorte->addDepartment($this->getReference('c-code-343-d-code-'.'54'));

        $manager->persist($EntityCcfComfanorte);

        $EntityCcfComfamiliarPutumayo = new Entity();
        $EntityCcfComfamiliarPutumayo->setName('CCF COMFAMILIAR PUTUMAYO ');
        $EntityCcfComfamiliarPutumayo->setPayrollCode('584');
        $EntityCcfComfamiliarPutumayo->setPilaCode('CFF63');
        $EntityCcfComfamiliarPutumayo->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfamiliarPutumayo->addDepartment($this->getReference('c-code-343-d-code-'.'86'));

        $manager->persist($EntityCcfComfamiliarPutumayo);

        $EntityCcfCajasan = new Entity();
        $EntityCcfCajasan->setName('CCF CAJASAN ');
        $EntityCcfCajasan->setPayrollCode('588');
        $EntityCcfCajasan->setPilaCode('CCF39');
        $EntityCcfCajasan->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfCajasan->addDepartment($this->getReference('c-code-343-d-code-'.'68'));

        $manager->persist($EntityCcfCajasan);

        $EntityCcfCombarranquilla = new Entity();
        $EntityCcfCombarranquilla->setName('CCF COMBARRANQUILLA ');
        $EntityCcfCombarranquilla->setPayrollCode('590');
        $EntityCcfCombarranquilla->setPilaCode('CCF06');
        $EntityCcfCombarranquilla->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfCombarranquilla->addDepartment($this->getReference('c-code-343-d-code-'.'08'));

        $manager->persist($EntityCcfCombarranquilla);

        $EntityCcfCafasur = new Entity();
        $EntityCcfCafasur->setName('CCF CAFASUR');
        $EntityCcfCafasur->setPayrollCode('592');
        $EntityCcfCafasur->setPilaCode('CFF46');
        $EntityCcfCafasur->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfCafasur->addDepartment($this->getReference('c-code-343-d-code-'.'73'));

        $manager->persist($EntityCcfCafasur);

        $EntityCcfComfatolima = new Entity();
        $EntityCcfComfatolima->setName('CCF COMFATOLIMA ');
        $EntityCcfComfatolima->setPayrollCode('594');
        $EntityCcfComfatolima->setPilaCode('CFF48');
        $EntityCcfComfatolima->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfatolima->addDepartment($this->getReference('c-code-343-d-code-'.'73'));

        $manager->persist($EntityCcfComfatolima);

        $EntityCcfComfacasanare = new Entity();
        $EntityCcfComfacasanare->setName('CCF COMFACASANARE');
        $EntityCcfComfacasanare->setPayrollCode('598');
        $EntityCcfComfacasanare->setPilaCode('CCF69');
        $EntityCcfComfacasanare->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfacasanare->addDepartment($this->getReference('c-code-343-d-code-'.'85'));

        $manager->persist($EntityCcfComfacasanare);

        /* Type ars */

        $EntityArsAliansalud = new Entity();
        $EntityArsAliansalud->setName('ARS ALIANSALUD ');
        $EntityArsAliansalud->setPayrollCode('95');
        $EntityArsAliansalud->setPilaCode('EPS001');
        $EntityArsAliansalud->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsAliansalud);

        $EntityArsComfachoco = new Entity();
        $EntityArsComfachoco->setName('ARS COMFACHOCO');
        $EntityArsComfachoco->setPayrollCode('95');
        $EntityArsComfachoco->setPilaCode('CCFC20');
        $EntityArsComfachoco->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsComfachoco);

        $EntityArsAnasWayuu = new Entity();
        $EntityArsAnasWayuu->setName('ARS ANAS WAYUU');
        $EntityArsAnasWayuu->setPayrollCode('95');
        $EntityArsAnasWayuu->setPilaCode('EPSIC4');
        $EntityArsAnasWayuu->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsAnasWayuu);

        $EntityArsComparta = new Entity();
        $EntityArsComparta->setName('ARS COMPARTA');
        $EntityArsComparta->setPayrollCode('95');
        $EntityArsComparta->setPilaCode('ESSC33');
        $EntityArsComparta->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsComparta);

        $EntityArsFosyga = new Entity();
        $EntityArsFosyga->setName('ARS FOSYGA');
        $EntityArsFosyga->setPayrollCode('95');
        $EntityArsFosyga->setPilaCode('MIN001');
        $EntityArsFosyga->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsFosyga);

        $EntityArsCafesaludLiqSaludcop = new Entity();
        $EntityArsCafesaludLiqSaludcop->setName('ARS CAFESALUD - LIQ. SALUDCOP');
        $EntityArsCafesaludLiqSaludcop->setPayrollCode('95');
        $EntityArsCafesaludLiqSaludcop->setPilaCode('EPS003');
        $EntityArsCafesaludLiqSaludcop->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsCafesaludLiqSaludcop);

        $EntityArsCapresoca = new Entity();
        $EntityArsCapresoca->setName('ARS CAPRESOCA');
        $EntityArsCapresoca->setPayrollCode('95');
        $EntityArsCapresoca->setPilaCode('EPS025');
        $EntityArsCapresoca->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsCapresoca);

        $EntityArsComfenalcoValle = new Entity();
        $EntityArsComfenalcoValle->setName('ARS COMFENALCO VALLE ');
        $EntityArsComfenalcoValle->setPayrollCode('95');
        $EntityArsComfenalcoValle->setPilaCode('EPS012');
        $EntityArsComfenalcoValle->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsComfenalcoValle);

        $EntityArsCompensar = new Entity();
        $EntityArsCompensar->setName('ARS COMPENSAR');
        $EntityArsCompensar->setPayrollCode('95');
        $EntityArsCompensar->setPilaCode('EPS008 ');
        $EntityArsCompensar->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsCompensar);

        $EntityArsCoomeva = new Entity();
        $EntityArsCoomeva->setName('ARS COOMEVA');
        $EntityArsCoomeva->setPayrollCode('95');
        $EntityArsCoomeva->setPilaCode('EPS016');
        $EntityArsCoomeva->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsCoomeva);

        $EntityArsCruzblanca = new Entity();
        $EntityArsCruzblanca->setName('ARS CRUZBLANCA');
        $EntityArsCruzblanca->setPayrollCode('95');
        $EntityArsCruzblanca->setPilaCode('EPS023');
        $EntityArsCruzblanca->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsCruzblanca);

        $EntityArsFamisanar = new Entity();
        $EntityArsFamisanar->setName('ARS FAMISANAR');
        $EntityArsFamisanar->setPayrollCode('95');
        $EntityArsFamisanar->setPilaCode('EPS017');
        $EntityArsFamisanar->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsFamisanar);

        $EntityArsNuevaEps = new Entity();
        $EntityArsNuevaEps->setName('ARS NUEVA EPS ');
        $EntityArsNuevaEps->setPayrollCode('95');
        $EntityArsNuevaEps->setPilaCode('EPS037');
        $EntityArsNuevaEps->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsNuevaEps);

        $EntityArsSaludColpatria = new Entity();
        $EntityArsSaludColpatria->setName('ARS SALUD COLPATRIA');
        $EntityArsSaludColpatria->setPayrollCode('95');
        $EntityArsSaludColpatria->setPilaCode('EPS015');
        $EntityArsSaludColpatria->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsSaludColpatria);

        $EntityArsSaludtototal = new Entity();
        $EntityArsSaludtototal->setName('ARS SALUDTOTOTAL');
        $EntityArsSaludtototal->setPayrollCode('95');
        $EntityArsSaludtototal->setPilaCode('EPS002');
        $EntityArsSaludtototal->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsSaludtototal);

        $EntityArsSaludvida = new Entity();
        $EntityArsSaludvida->setName('ARS SALUDVIDA');
        $EntityArsSaludvida->setPayrollCode('95');
        $EntityArsSaludvida->setPilaCode('EPS033');
        $EntityArsSaludvida->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsSaludvida);

        $EntityArsSanitas = new Entity();
        $EntityArsSanitas->setName('ARS SANITAS');
        $EntityArsSanitas->setPayrollCode('95');
        $EntityArsSanitas->setPilaCode('EPS005');
        $EntityArsSanitas->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsSanitas);

        $EntityArsSOSServicioOccidentalDeSalud = new Entity();
        $EntityArsSOSServicioOccidentalDeSalud->setName('ARS S.O.S - SERVICIO OCCIDENTAL DE SALUD');
        $EntityArsSOSServicioOccidentalDeSalud->setPayrollCode('95');
        $EntityArsSOSServicioOccidentalDeSalud->setPilaCode('EPS018');
        $EntityArsSOSServicioOccidentalDeSalud->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsSOSServicioOccidentalDeSalud);

        $EntityArsSura = new Entity();
        $EntityArsSura->setName('ARS SURA');
        $EntityArsSura->setPayrollCode('95');
        $EntityArsSura->setPilaCode('EPS010');
        $EntityArsSura->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsSura);

        $EntityArsEcoopsosEntidadCooperativaSolidariaDeSalud = new Entity();
        $EntityArsEcoopsosEntidadCooperativaSolidariaDeSalud->setName('ARS ECOOPSOS - ENTIDAD COOPERATIVA SOLIDARIA DE SALUD');
        $EntityArsEcoopsosEntidadCooperativaSolidariaDeSalud->setPayrollCode('95');
        $EntityArsEcoopsosEntidadCooperativaSolidariaDeSalud->setPilaCode('ESSC91');
        $EntityArsEcoopsosEntidadCooperativaSolidariaDeSalud->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsEcoopsosEntidadCooperativaSolidariaDeSalud);

        $EntityArsMutualSer = new Entity();
        $EntityArsMutualSer->setName('ARS MUTUAL SER ');
        $EntityArsMutualSer->setPayrollCode('95');
        $EntityArsMutualSer->setPilaCode('ESSC07');
        $EntityArsMutualSer->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsMutualSer);

        $EntityArsCafam = new Entity();
        $EntityArsCafam->setName('ARS CAFAM');
        $EntityArsCafam->setPayrollCode('95');
        $EntityArsCafam->setPilaCode('CCFC18');
        $EntityArsCafam->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsCafam);

        $EntityArsAmbuq = new Entity();
        $EntityArsAmbuq->setName('ARS AMBUQ');
        $EntityArsAmbuq->setPayrollCode('95');
        $EntityArsAmbuq->setPilaCode('ESSC76');
        $EntityArsAmbuq->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsAmbuq);

        $EntityArsComfanarino = new Entity();
        $EntityArsComfanarino->setName('ARS COMFANARIÑO ');
        $EntityArsComfanarino->setPayrollCode('95');
        $EntityArsComfanarino->setPilaCode('CFFC27');
        $EntityArsComfanarino->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsComfanarino);

        $EntityArsMallamas = new Entity();
        $EntityArsMallamas->setName('ARS MALLAMAS');
        $EntityArsMallamas->setPayrollCode('95');
        $EntityArsMallamas->setPilaCode('EPSIC5');
        $EntityArsMallamas->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsMallamas);

        $EntityArsPijaosalud = new Entity();
        $EntityArsPijaosalud->setName('ARS PIJAOSALUD');
        $EntityArsPijaosalud->setPayrollCode('95');
        $EntityArsPijaosalud->setPilaCode('EPSIC6');
        $EntityArsPijaosalud->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsPijaosalud);

        $EntityArsAsmetSalud = new Entity();
        $EntityArsAsmetSalud->setName('ARS ASMET SALUD');
        $EntityArsAsmetSalud->setPayrollCode('95');
        $EntityArsAsmetSalud->setPilaCode('ESSC62');
        $EntityArsAsmetSalud->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsAsmetSalud);

        $EntityArsComfamiliarHuila = new Entity();
        $EntityArsComfamiliarHuila->setName('ARS COMFAMILIAR HUILA');
        $EntityArsComfamiliarHuila->setPayrollCode('95');
        $EntityArsComfamiliarHuila->setPilaCode('CFFC24');
        $EntityArsComfamiliarHuila->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsComfamiliarHuila);

        $EntityArsComfacor = new Entity();
        $EntityArsComfacor->setName('ARS COMFACOR');
        $EntityArsComfacor->setPayrollCode('95');
        $EntityArsComfacor->setPilaCode('CCFC15');
        $EntityArsComfacor->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsComfacor);

        $EntityArsCajacopi = new Entity();
        $EntityArsCajacopi->setName('ARS CAJACOPI');
        $EntityArsCajacopi->setPayrollCode('95');
        $EntityArsCajacopi->setPilaCode('CFFC55');
        $EntityArsCajacopi->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsCajacopi);

        $EntityArsConvida = new Entity();
        $EntityArsConvida->setName('ARS CONVIDA');
        $EntityArsConvida->setPayrollCode('95');
        $EntityArsConvida->setPilaCode('EPSC22');
        $EntityArsConvida->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsConvida);

        $EntityArsSaviaSalud = new Entity();
        $EntityArsSaviaSalud->setName('ARS SAVIA SALUD ');
        $EntityArsSaviaSalud->setPayrollCode('95');
        $EntityArsSaviaSalud->setPilaCode('CCFC02');
        $EntityArsSaviaSalud->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsSaviaSalud);

        $EntityArsEmssanar = new Entity();
        $EntityArsEmssanar->setName('ARS EMSSANAR');
        $EntityArsEmssanar->setPayrollCode('95');
        $EntityArsEmssanar->setPilaCode('ESSC18');
        $EntityArsEmssanar->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsEmssanar);

        $EntityArsCoosalud = new Entity();
        $EntityArsCoosalud->setName('ARS COOSALUD');
        $EntityArsCoosalud->setPayrollCode('95');
        $EntityArsCoosalud->setPilaCode('ESSC24');
        $EntityArsCoosalud->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsCoosalud);

        $EntityArsColsubsidio = new Entity();
        $EntityArsColsubsidio->setName('ARS COLSUBSIDIO');
        $EntityArsColsubsidio->setPayrollCode('95');
        $EntityArsColsubsidio->setPilaCode('CCFC10');
        $EntityArsColsubsidio->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsColsubsidio);

        $EntityArsComfacundi = new Entity();
        $EntityArsComfacundi->setName('ARS COMFACUNDI');
        $EntityArsComfacundi->setPayrollCode('95');
        $EntityArsComfacundi->setPilaCode('CFFC53');
        $EntityArsComfacundi->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsComfacundi);

        $EntityArsCapitalSalud = new Entity();
        $EntityArsCapitalSalud->setName('ARS CAPITAL SALUD ');
        $EntityArsCapitalSalud->setPayrollCode('95');
        $EntityArsCapitalSalud->setPilaCode('EPSC34');
        $EntityArsCapitalSalud->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsCapitalSalud);

        $EntityArsManexka = new Entity();
        $EntityArsManexka->setName('ARS MANEXKA');
        $EntityArsManexka->setPayrollCode('95');
        $EntityArsManexka->setPilaCode('EPSIC2');
        $EntityArsManexka->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsManexka);

        $EntityArsEmdisalud = new Entity();
        $EntityArsEmdisalud->setName('ARS EMDISALUD');
        $EntityArsEmdisalud->setPayrollCode('95');
        $EntityArsEmdisalud->setPilaCode('ESSC02');
        $EntityArsEmdisalud->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsEmdisalud);

        $manager->flush();

        
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}
