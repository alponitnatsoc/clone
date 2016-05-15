<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Entity;

class LoadEntityData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Add all the entities to the database
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {

        /* Type pensiones */

        $EntityAfpNoAporta = new Entity();
        $EntityAfpNoAporta->setName('NO APORTA');
        $EntityAfpNoAporta->setPayrollCode('0');
        $EntityAfpNoAporta->setPilaCode('0');
        $EntityAfpNoAporta->setEntityTypeEntityType($this->getReference('entityType-pensiones'));


        $manager->persist($EntityAfpNoAporta);

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

        $EntityAfpOldMutualFondoAlternativoDePensiones = new Entity();
        $EntityAfpOldMutualFondoAlternativoDePensiones->setName('AFP OLD MUTUAL FONDO ALTERNATIVO DE PENSIONES ');
        $EntityAfpOldMutualFondoAlternativoDePensiones->setPayrollCode('370');
        $EntityAfpOldMutualFondoAlternativoDePensiones->setPilaCode('230904');
        $EntityAfpOldMutualFondoAlternativoDePensiones->setEntityTypeEntityType($this->getReference('entityType-pensiones'));


        $manager->persist($EntityAfpOldMutualFondoAlternativoDePensiones);

        $EntityAfpColpensiones = new Entity();
        $EntityAfpColpensiones->setName('AFP COLPENSIONES');
        $EntityAfpColpensiones->setPayrollCode('102');
        $EntityAfpColpensiones->setPilaCode('25-14');
        $EntityAfpColpensiones->setEntityTypeEntityType($this->getReference('entityType-pensiones'));


        $manager->persist($EntityAfpColpensiones);

        /* Type arl */

        $EntityArlPositivaCompaniaDeSeguros = new Entity();
        $EntityArlPositivaCompaniaDeSeguros->setName('ARL POSITIVA COMPAÑÍA DE SEGUROS');
        $EntityArlPositivaCompaniaDeSeguros->setPayrollCode('100');
        $EntityArlPositivaCompaniaDeSeguros->setPilaCode('14-23');
        $EntityArlPositivaCompaniaDeSeguros->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArlPositivaCompaniaDeSeguros);

        $EntityArlAlfa = new Entity();
        $EntityArlAlfa->setName('ARL ALFA');
        $EntityArlAlfa->setPayrollCode('600');
        $EntityArlAlfa->setPilaCode('14-17');
        $EntityArlAlfa->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArlAlfa);

        $EntityArlSegurosDeVidaAurora = new Entity();
        $EntityArlSegurosDeVidaAurora->setName('ARL SEGUROS DE VIDA AURORA');
        $EntityArlSegurosDeVidaAurora->setPayrollCode('615');
        $EntityArlSegurosDeVidaAurora->setPilaCode('14-8');
        $EntityArlSegurosDeVidaAurora->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArlSegurosDeVidaAurora);

        $EntityArlSegurosBolivar = new Entity();
        $EntityArlSegurosBolivar->setName('ARL SEGUROS BOLIVAR');
        $EntityArlSegurosBolivar->setPayrollCode('625');
        $EntityArlSegurosBolivar->setPilaCode('14-7');
        $EntityArlSegurosBolivar->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArlSegurosBolivar);

        $EntityArlColmena = new Entity();
        $EntityArlColmena->setName('ARL COLMENA');
        $EntityArlColmena->setPayrollCode('630');
        $EntityArlColmena->setPilaCode('14-25');
        $EntityArlColmena->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArlColmena);

        $EntityArlColpatria = new Entity();
        $EntityArlColpatria->setName('ARL COLPATRIA');
        $EntityArlColpatria->setPayrollCode('635');
        $EntityArlColpatria->setPilaCode('14-4');
        $EntityArlColpatria->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArlColpatria);

        $EntityArlLaEquidad = new Entity();
        $EntityArlLaEquidad->setName('ARL LA EQUIDAD');
        $EntityArlLaEquidad->setPayrollCode('645');
        $EntityArlLaEquidad->setPilaCode('14-29');
        $EntityArlLaEquidad->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArlLaEquidad);

        $EntityArlLibertySeguros = new Entity();
        $EntityArlLibertySeguros->setName('ARL LIBERTY SEGUROS ');
        $EntityArlLibertySeguros->setPayrollCode('655');
        $EntityArlLibertySeguros->setPilaCode('14-18');
        $EntityArlLibertySeguros->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArlLibertySeguros);

        $EntityArlSura = new Entity();
        $EntityArlSura->setName('ARL SURA ');
        $EntityArlSura->setPayrollCode('670');
        $EntityArlSura->setPilaCode('14-28');
        $EntityArlSura->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArlSura);

        $EntityArlMapfre = new Entity();
        $EntityArlMapfre->setName('ARL MAPFRE');
        $EntityArlMapfre->setPayrollCode('');
        $EntityArlMapfre->setPilaCode('14-30');
        $EntityArlMapfre->setEntityTypeEntityType($this->getReference('entityType-arl'));


        $manager->persist($EntityArlMapfre);

        /* Type cajacomp */

        $EntityCcfColsubsidio = new Entity();
        $EntityCcfColsubsidio->setName('CCF COLSUBSIDIO');
        $EntityCcfColsubsidio->setPayrollCode('500');
        $EntityCcfColsubsidio->setPilaCode('CCF22');
        $EntityCcfColsubsidio->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfColsubsidio->addDepartment($this->getReference('c-code-343-d-code-'.'05'));

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

        $EntityCcfComfenalcoCartagena = new Entity();
        $EntityCcfComfenalcoCartagena->setName('CCF COMFENALCO CARTAGENA');
        $EntityCcfComfenalcoCartagena->setPayrollCode('516');
        $EntityCcfComfenalcoCartagena->setPilaCode('CCF08');
        $EntityCcfComfenalcoCartagena->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfenalcoCartagena->addDepartment($this->getReference('c-code-343-d-code-'.'13'));

        $manager->persist($EntityCcfComfenalcoCartagena);

        $EntityCcfComfiar = new Entity();
        $EntityCcfComfiar->setName('CCF COMFIAR');
        $EntityCcfComfiar->setPayrollCode('526');
        $EntityCcfComfiar->setPilaCode('CCF67');
        $EntityCcfComfiar->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfiar->addDepartment($this->getReference('c-code-343-d-code-'.'81'));

        $manager->persist($EntityCcfComfiar);

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

        $EntityCcfConfacundi = new Entity();
        $EntityCcfConfacundi->setName('CCF CONFACUNDI ');
        $EntityCcfConfacundi->setPayrollCode('548');
        $EntityCcfConfacundi->setPilaCode('CCF26');
        $EntityCcfConfacundi->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfConfacundi->addDepartment($this->getReference('c-code-343-d-code-'.'25'));
        $EntityCcfConfacundi->addDepartment($this->getReference('c-code-343-d-code-'.'11'));

        $manager->persist($EntityCcfConfacundi);

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

        $EntityCcfComfaguajira = new Entity();
        $EntityCcfComfaguajira->setName('CCF COMFAGUAJIRA');
        $EntityCcfComfaguajira->setPayrollCode(' ');
        $EntityCcfComfaguajira->setPilaCode('CFF30');
        $EntityCcfComfaguajira->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfaguajira->addDepartment($this->getReference('c-code-343-d-code-'.'44'));

        $manager->persist($EntityCcfComfaguajira);

        $EntityCcfComcaja = new Entity();
        $EntityCcfComcaja->setName('CCF COMCAJA');
        $EntityCcfComcaja->setPayrollCode('');
        $EntityCcfComcaja->setPilaCode('CCF68');
        $EntityCcfComcaja->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComcaja->addDepartment($this->getReference('c-code-343-d-code-'.'94'));
        $EntityCcfComcaja->addDepartment($this->getReference('c-code-343-d-code-'.'95'));
        $EntityCcfComcaja->addDepartment($this->getReference('c-code-343-d-code-'.'97'));
        $EntityCcfComcaja->addDepartment($this->getReference('c-code-343-d-code-'.'99'));

        $manager->persist($EntityCcfComcaja);

        $EntityCcfComfamiliarHuila = new Entity();
        $EntityCcfComfamiliarHuila->setName('CCF COMFAMILIAR HUILA ');
        $EntityCcfComfamiliarHuila->setPayrollCode('');
        $EntityCcfComfamiliarHuila->setPilaCode('CFF32');
        $EntityCcfComfamiliarHuila->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfamiliarHuila->addDepartment($this->getReference('c-code-343-d-code-'.'41'));

        $manager->persist($EntityCcfComfamiliarHuila);

        $EntityCcfComfamiliarPutumayo = new Entity();
        $EntityCcfComfamiliarPutumayo->setName('CCF COMFAMILIAR PUTUMAYO ');
        $EntityCcfComfamiliarPutumayo->setPayrollCode('');
        $EntityCcfComfamiliarPutumayo->setPilaCode('CFF63');
        $EntityCcfComfamiliarPutumayo->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfamiliarPutumayo->addDepartment($this->getReference('c-code-343-d-code-'.'86'));

        $manager->persist($EntityCcfComfamiliarPutumayo);

        $EntityCcfComfaoriente = new Entity();
        $EntityCcfComfaoriente->setName('CCF COMFAORIENTE ');
        $EntityCcfComfaoriente->setPayrollCode('');
        $EntityCcfComfaoriente->setPilaCode('CFF36');
        $EntityCcfComfaoriente->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfaoriente->addDepartment($this->getReference('c-code-343-d-code-'.'54'));

        $manager->persist($EntityCcfComfaoriente);

        $EntityCcfComfasucre = new Entity();
        $EntityCcfComfasucre->setName('CCF COMFASUCRE');
        $EntityCcfComfasucre->setPayrollCode('');
        $EntityCcfComfasucre->setPilaCode('CCF41');
        $EntityCcfComfasucre->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfasucre->addDepartment($this->getReference('c-code-343-d-code-'.'70'));

        $manager->persist($EntityCcfComfasucre);

        $EntityCcfComfenalco = new Entity();
        $EntityCcfComfenalco->setName('CCF COMFENALCO ');
        $EntityCcfComfenalco->setPayrollCode('');
        $EntityCcfComfenalco->setPilaCode('CCF03');
        $EntityCcfComfenalco->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));
        $EntityCcfComfenalco->addDepartment($this->getReference('c-code-343-d-code-'.'05'));

        $manager->persist($EntityCcfComfenalco);

        /* Type eps */

        $EntityEpsFosyga = new Entity();
        $EntityEpsFosyga->setName('EPS FOSYGA');
        $EntityEpsFosyga->setPayrollCode('5');
        $EntityEpsFosyga->setPilaCode('MIN001');
        $EntityEpsFosyga->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsFosyga);

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

        $EntityEpsAliansalud = new Entity();
        $EntityEpsAliansalud->setName('EPS ALIANSALUD ');
        $EntityEpsAliansalud->setPayrollCode('121');
        $EntityEpsAliansalud->setPilaCode('EPS001');
        $EntityEpsAliansalud->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsAliansalud);

        $EntityEpsSanitas = new Entity();
        $EntityEpsSanitas->setName('EPS SANITAS');
        $EntityEpsSanitas->setPayrollCode('150');
        $EntityEpsSanitas->setPilaCode('EPS005');
        $EntityEpsSanitas->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsSanitas);

        $EntityEpsSOS = new Entity();
        $EntityEpsSOS->setName('EPS S.O.S');
        $EntityEpsSOS->setPayrollCode('160');
        $EntityEpsSOS->setPilaCode('EPS018');
        $EntityEpsSOS->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsSOS);

        $EntityEpsSura = new Entity();
        $EntityEpsSura->setName('EPS SURA');
        $EntityEpsSura->setPayrollCode('170');
        $EntityEpsSura->setPilaCode('EPS010');
        $EntityEpsSura->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsSura);

        $EntityEpsCafesaludLiqSaludcop = new Entity();
        $EntityEpsCafesaludLiqSaludcop->setName('EPS CAFESALUD - LIQ. SALUDCOP');
        $EntityEpsCafesaludLiqSaludcop->setPayrollCode('10');
        $EntityEpsCafesaludLiqSaludcop->setPilaCode('EPS003');
        $EntityEpsCafesaludLiqSaludcop->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsCafesaludLiqSaludcop);

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

        $EntityEpsSaludColpatria = new Entity();
        $EntityEpsSaludColpatria->setName('EPS SALUD COLPATRIA');
        $EntityEpsSaludColpatria->setPayrollCode('120');
        $EntityEpsSaludColpatria->setPilaCode('EPS015');
        $EntityEpsSaludColpatria->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsSaludColpatria);

        $EntityEpsCruzblanca = new Entity();
        $EntityEpsCruzblanca->setName('EPS CRUZBLANCA');
        $EntityEpsCruzblanca->setPayrollCode('80');
        $EntityEpsCruzblanca->setPilaCode('EPS023');
        $EntityEpsCruzblanca->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsCruzblanca);

        $EntityEpsMultimedicasSaludConCalidadEpsSA = new Entity();
        $EntityEpsMultimedicasSaludConCalidadEpsSA->setName('EPS MULTIMEDICAS SALUD CON CALIDAD EPS S.A.');
        $EntityEpsMultimedicasSaludConCalidadEpsSA->setPayrollCode('180');
        $EntityEpsMultimedicasSaludConCalidadEpsSA->setPilaCode('EPS038');
        $EntityEpsMultimedicasSaludConCalidadEpsSA->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsMultimedicasSaludConCalidadEpsSA);

        $EntityEpsGoldenGroupEps = new Entity();
        $EntityEpsGoldenGroupEps->setName('EPS GOLDEN GROUP EPS');
        $EntityEpsGoldenGroupEps->setPayrollCode('185');
        $EntityEpsGoldenGroupEps->setPilaCode('EPS039');
        $EntityEpsGoldenGroupEps->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsGoldenGroupEps);

        $EntityEpsSaviaSalud = new Entity();
        $EntityEpsSaviaSalud->setName('EPS SAVIA SALUD ');
        $EntityEpsSaviaSalud->setPayrollCode('250');
        $EntityEpsSaviaSalud->setPilaCode('CCFC02');
        $EntityEpsSaviaSalud->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsSaviaSalud);

        $EntityEpsColsubsidio = new Entity();
        $EntityEpsColsubsidio->setName('EPS COLSUBSIDIO');
        $EntityEpsColsubsidio->setPayrollCode('270');
        $EntityEpsColsubsidio->setPilaCode('CCFC10');
        $EntityEpsColsubsidio->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsColsubsidio);

        $EntityEpsComfacor = new Entity();
        $EntityEpsComfacor->setName('EPS COMFACOR');
        $EntityEpsComfacor->setPayrollCode('235');
        $EntityEpsComfacor->setPilaCode('CCFC15');
        $EntityEpsComfacor->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsComfacor);

        $EntityEpsCafam = new Entity();
        $EntityEpsCafam->setName('EPS CAFAM');
        $EntityEpsCafam->setPayrollCode('201');
        $EntityEpsCafam->setPilaCode('CCFC18');
        $EntityEpsCafam->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsCafam);

        $EntityEpsComfachoco = new Entity();
        $EntityEpsComfachoco->setName('EPS COMFACHOCO');
        $EntityEpsComfachoco->setPayrollCode('209');
        $EntityEpsComfachoco->setPilaCode('CCFC20');
        $EntityEpsComfachoco->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsComfachoco);

        $EntityEpsComfamiliarHuila = new Entity();
        $EntityEpsComfamiliarHuila->setName('EPS COMFAMILIAR HUILA');
        $EntityEpsComfamiliarHuila->setPayrollCode('230');
        $EntityEpsComfamiliarHuila->setPilaCode('CFFC24');
        $EntityEpsComfamiliarHuila->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsComfamiliarHuila);

        $EntityEpsComfamiliarNarino = new Entity();
        $EntityEpsComfamiliarNarino->setName('EPS COMFAMILIAR NARIÑO ');
        $EntityEpsComfamiliarNarino->setPayrollCode('210');
        $EntityEpsComfamiliarNarino->setPilaCode('CFFC27');
        $EntityEpsComfamiliarNarino->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsComfamiliarNarino);

        $EntityEpsComfacundi = new Entity();
        $EntityEpsComfacundi->setName('EPS COMFACUNDI');
        $EntityEpsComfacundi->setPayrollCode('275');
        $EntityEpsComfacundi->setPilaCode('CFFC53');
        $EntityEpsComfacundi->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsComfacundi);

        $EntityEpsCajacopiAtlantico = new Entity();
        $EntityEpsCajacopiAtlantico->setName('EPS CAJACOPI / ATLANTICO');
        $EntityEpsCajacopiAtlantico->setPayrollCode('240');
        $EntityEpsCajacopiAtlantico->setPilaCode('CFFC55');
        $EntityEpsCajacopiAtlantico->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsCajacopiAtlantico);

        $EntityEpsCaprecom = new Entity();
        $EntityEpsCaprecom->setName('EPS CAPRECOM');
        $EntityEpsCaprecom->setPayrollCode('16');
        $EntityEpsCaprecom->setPilaCode('EPSC20');
        $EntityEpsCaprecom->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsCaprecom);

        $EntityEpsConvida = new Entity();
        $EntityEpsConvida->setName('EPS CONVIDA');
        $EntityEpsConvida->setPayrollCode('58');
        $EntityEpsConvida->setPilaCode('EPSC22');
        $EntityEpsConvida->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsConvida);

        $EntityEpsCapresoca = new Entity();
        $EntityEpsCapresoca->setName('EPS CAPRESOCA');
        $EntityEpsCapresoca->setPayrollCode('18');
        $EntityEpsCapresoca->setPilaCode('EPSC25');
        $EntityEpsCapresoca->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsCapresoca);

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

        $EntityEpsAnasWayuu = new Entity();
        $EntityEpsAnasWayuu->setName('EPS ANAS WAYUU');
        $EntityEpsAnasWayuu->setPayrollCode('219');
        $EntityEpsAnasWayuu->setPilaCode('EPSIC4');
        $EntityEpsAnasWayuu->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsAnasWayuu);

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

        $EntityEpsEmdisalud = new Entity();
        $EntityEpsEmdisalud->setName('EPS EMDISALUD');
        $EntityEpsEmdisalud->setPayrollCode('295');
        $EntityEpsEmdisalud->setPilaCode('ESSC02');
        $EntityEpsEmdisalud->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsEmdisalud);

        $EntityEpsMutualSer = new Entity();
        $EntityEpsMutualSer->setName('EPS MUTUAL SER ');
        $EntityEpsMutualSer->setPayrollCode('195');
        $EntityEpsMutualSer->setPilaCode('ESSC07');
        $EntityEpsMutualSer->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsMutualSer);

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

        $EntityEpsComparta = new Entity();
        $EntityEpsComparta->setName('EPS COMPARTA');
        $EntityEpsComparta->setPayrollCode('231');
        $EntityEpsComparta->setPilaCode('ESSC33');
        $EntityEpsComparta->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsComparta);

        $EntityEpsAsmetSalud = new Entity();
        $EntityEpsAsmetSalud->setName('EPS ASMET SALUD');
        $EntityEpsAsmetSalud->setPayrollCode('225');
        $EntityEpsAsmetSalud->setPilaCode('ESSC62');
        $EntityEpsAsmetSalud->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsAsmetSalud);

        $EntityEpsAmbuq = new Entity();
        $EntityEpsAmbuq->setName('EPS AMBUQ');
        $EntityEpsAmbuq->setPayrollCode('205');
        $EntityEpsAmbuq->setPilaCode('ESSC76');
        $EntityEpsAmbuq->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsAmbuq);

        $EntityEpsEcoopsos = new Entity();
        $EntityEpsEcoopsos->setName('EPS ECOOPSOS');
        $EntityEpsEcoopsos->setPayrollCode('190');
        $EntityEpsEcoopsos->setPilaCode('ESSC91');
        $EntityEpsEcoopsos->setEntityTypeEntityType($this->getReference('entityType-eps'));


        $manager->persist($EntityEpsEcoopsos);

        /* Type ars */

        $EntityArsSaviaSalud = new Entity();
        $EntityArsSaviaSalud->setName('ARS SAVIA SALUD ');
        $EntityArsSaviaSalud->setPayrollCode('95');
        $EntityArsSaviaSalud->setPilaCode('CCFC02');
        $EntityArsSaviaSalud->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsSaviaSalud);

        $EntityArsColsubsidio = new Entity();
        $EntityArsColsubsidio->setName('ARS COLSUBSIDIO');
        $EntityArsColsubsidio->setPayrollCode('95');
        $EntityArsColsubsidio->setPilaCode('CCFC10');
        $EntityArsColsubsidio->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsColsubsidio);

        $EntityArsComfacor = new Entity();
        $EntityArsComfacor->setName('ARS COMFACOR');
        $EntityArsComfacor->setPayrollCode('95');
        $EntityArsComfacor->setPilaCode('CCFC15');
        $EntityArsComfacor->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsComfacor);

        $EntityArsCafam = new Entity();
        $EntityArsCafam->setName('ARS CAFAM');
        $EntityArsCafam->setPayrollCode('95');
        $EntityArsCafam->setPilaCode('CCFC18');
        $EntityArsCafam->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsCafam);

        $EntityArsComfachoco = new Entity();
        $EntityArsComfachoco->setName('ARS COMFACHOCO');
        $EntityArsComfachoco->setPayrollCode('95');
        $EntityArsComfachoco->setPilaCode('CCFC20');
        $EntityArsComfachoco->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsComfachoco);

        $EntityArsComfamiliarHuila = new Entity();
        $EntityArsComfamiliarHuila->setName('ARS COMFAMILIAR HUILA');
        $EntityArsComfamiliarHuila->setPayrollCode('95');
        $EntityArsComfamiliarHuila->setPilaCode('CFFC24');
        $EntityArsComfamiliarHuila->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsComfamiliarHuila);

        $EntityArsComfamiliarNarino = new Entity();
        $EntityArsComfamiliarNarino->setName('ARS COMFAMILIAR NARIÑO ');
        $EntityArsComfamiliarNarino->setPayrollCode('95');
        $EntityArsComfamiliarNarino->setPilaCode('CFFC27');
        $EntityArsComfamiliarNarino->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsComfamiliarNarino);

        $EntityArsComfacundi = new Entity();
        $EntityArsComfacundi->setName('ARS COMFACUNDI');
        $EntityArsComfacundi->setPayrollCode('95');
        $EntityArsComfacundi->setPilaCode('CFFC53');
        $EntityArsComfacundi->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsComfacundi);

        $EntityArsCajacopiAtlantico = new Entity();
        $EntityArsCajacopiAtlantico->setName('ARS CAJACOPI / ATLANTICO');
        $EntityArsCajacopiAtlantico->setPayrollCode('95');
        $EntityArsCajacopiAtlantico->setPilaCode('CFFC55');
        $EntityArsCajacopiAtlantico->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsCajacopiAtlantico);

        $EntityArsCaprecom = new Entity();
        $EntityArsCaprecom->setName('ARS CAPRECOM');
        $EntityArsCaprecom->setPayrollCode('95');
        $EntityArsCaprecom->setPilaCode('EPSC20');
        $EntityArsCaprecom->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsCaprecom);

        $EntityArsConvida = new Entity();
        $EntityArsConvida->setName('ARS CONVIDA');
        $EntityArsConvida->setPayrollCode('95');
        $EntityArsConvida->setPilaCode('EPSC22');
        $EntityArsConvida->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsConvida);

        $EntityArsCapresoca = new Entity();
        $EntityArsCapresoca->setName('ARS CAPRESOCA');
        $EntityArsCapresoca->setPayrollCode('95');
        $EntityArsCapresoca->setPilaCode('EPSC25');
        $EntityArsCapresoca->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsCapresoca);

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

        $EntityArsAnasWayuu = new Entity();
        $EntityArsAnasWayuu->setName('ARS ANAS WAYUU');
        $EntityArsAnasWayuu->setPayrollCode('95');
        $EntityArsAnasWayuu->setPilaCode('EPSIC4');
        $EntityArsAnasWayuu->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsAnasWayuu);

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

        $EntityArsEmdisalud = new Entity();
        $EntityArsEmdisalud->setName('ARS EMDISALUD');
        $EntityArsEmdisalud->setPayrollCode('95');
        $EntityArsEmdisalud->setPilaCode('ESSC02');
        $EntityArsEmdisalud->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsEmdisalud);

        $EntityArsMutualSer = new Entity();
        $EntityArsMutualSer->setName('ARS MUTUAL SER ');
        $EntityArsMutualSer->setPayrollCode('95');
        $EntityArsMutualSer->setPilaCode('ESSC07');
        $EntityArsMutualSer->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsMutualSer);

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

        $EntityArsComparta = new Entity();
        $EntityArsComparta->setName('ARS COMPARTA');
        $EntityArsComparta->setPayrollCode('95');
        $EntityArsComparta->setPilaCode('ESSC33');
        $EntityArsComparta->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsComparta);

        $EntityArsAsmetSalud = new Entity();
        $EntityArsAsmetSalud->setName('ARS ASMET SALUD');
        $EntityArsAsmetSalud->setPayrollCode('95');
        $EntityArsAsmetSalud->setPilaCode('ESSC62');
        $EntityArsAsmetSalud->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsAsmetSalud);

        $EntityArsAmbuq = new Entity();
        $EntityArsAmbuq->setName('ARS AMBUQ');
        $EntityArsAmbuq->setPayrollCode('95');
        $EntityArsAmbuq->setPilaCode('ESSC76');
        $EntityArsAmbuq->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsAmbuq);

        $EntityArsEcoopsos = new Entity();
        $EntityArsEcoopsos->setName('ARS ECOOPSOS');
        $EntityArsEcoopsos->setPayrollCode('95');
        $EntityArsEcoopsos->setPilaCode('ESSC91');
        $EntityArsEcoopsos->setEntityTypeEntityType($this->getReference('entityType-ars'));


        $manager->persist($EntityArsEcoopsos);


        $manager->flush();

        $this->addReference('entity-NoAporta-afp', $EntityAfpNoAporta);
        $this->addReference('entity-PorvenirHorizonte-afp', $EntityAfpPorvenirHorizonte);
        $this->addReference('entity-Colfondos-afp', $EntityAfpColfondos);
        $this->addReference('entity-ProteccionIng-afp', $EntityAfpProteccionIng);
        $this->addReference('entity-SkandiaOldMutualFondoDePensionesObligatorias-afp', $EntityAfpSkandiaOldMutualFondoDePensionesObligatorias);
        $this->addReference('entity-OldMutualFondoAlternativoDePensiones-afp', $EntityAfpOldMutualFondoAlternativoDePensiones);
        $this->addReference('entity-Colpensiones-afp', $EntityAfpColpensiones);
        $this->addReference('entity-PositivaCompaniaDeSeguros-arl', $EntityArlPositivaCompaniaDeSeguros);
        $this->addReference('entity-Alfa-arl', $EntityArlAlfa);
        $this->addReference('entity-SegurosDeVidaAurora-arl', $EntityArlSegurosDeVidaAurora);
        $this->addReference('entity-SegurosBolivar-arl', $EntityArlSegurosBolivar);
        $this->addReference('entity-Colmena-arl', $EntityArlColmena);
        $this->addReference('entity-Colpatria-arl', $EntityArlColpatria);
        $this->addReference('entity-LaEquidad-arl', $EntityArlLaEquidad);
        $this->addReference('entity-LibertySeguros-arl', $EntityArlLibertySeguros);
        $this->addReference('entity-Sura-arl', $EntityArlSura);
        $this->addReference('entity-Mapfre-arl', $EntityArlMapfre);
        $this->addReference('entity-Colsubsidio-ccf', $EntityCcfColsubsidio);
        $this->addReference('entity-Cafam-ccf', $EntityCcfCafam);
        $this->addReference('entity-Cafamaz-ccf', $EntityCcfCafamaz);
        $this->addReference('entity-ComfamiliarCamacol-ccf', $EntityCcfComfamiliarCamacol);
        $this->addReference('entity-Comfachoco-ccf', $EntityCcfComfachoco);
        $this->addReference('entity-ComfamiliarAtlantico-ccf', $EntityCcfComfamiliarAtlantico);
        $this->addReference('entity-ComfenalcoCartagena-ccf', $EntityCcfComfenalcoCartagena);
        $this->addReference('entity-Comfiar-ccf', $EntityCcfComfiar);
        $this->addReference('entity-ComfenalcoValle-ccf', $EntityCcfComfenalcoValle);
        $this->addReference('entity-Cajacopi-ccf', $EntityCcfCajacopi);
        $this->addReference('entity-ComfenalcoSantander-ccf', $EntityCcfComfenalcoSantander);
        $this->addReference('entity-ComfenalcoTolima-ccf', $EntityCcfComfenalcoTolima);
        $this->addReference('entity-Compensar-ccf', $EntityCcfCompensar);
        $this->addReference('entity-Cajamag-ccf', $EntityCcfCajamag);
        $this->addReference('entity-Cafaba-ccf', $EntityCcfCafaba);
        $this->addReference('entity-Comfaboy-ccf', $EntityCcfComfaboy);
        $this->addReference('entity-Comfamiliares-ccf', $EntityCcfComfamiliares);
        $this->addReference('entity-ComfamiliarCartagenaYBolivar-ccf', $EntityCcfComfamiliarCartagenaYBolivar);
        $this->addReference('entity-Comfacor-ccf', $EntityCcfComfacor);
        $this->addReference('entity-Confacundi-ccf', $EntityCcfConfacundi);
        $this->addReference('entity-ComfenalcoQuindio-ccf', $EntityCcfComfenalcoQuindio);
        $this->addReference('entity-Comfandi-ccf', $EntityCcfComfandi);
        $this->addReference('entity-ComfamiliarRisaralda-ccf', $EntityCcfComfamiliarRisaralda);
        $this->addReference('entity-CajaSai-ccf', $EntityCcfCajaSai);
        $this->addReference('entity-Comfaca-ccf', $EntityCcfComfaca);
        $this->addReference('entity-ComfamiliarNarino-ccf', $EntityCcfComfamiliarNarino);
        $this->addReference('entity-Comfacauca-ccf', $EntityCcfComfacauca);
        $this->addReference('entity-Comfacesar-ccf', $EntityCcfComfacesar);
        $this->addReference('entity-Cofrem-ccf', $EntityCcfCofrem);
        $this->addReference('entity-Comfama-ccf', $EntityCcfComfama);
        $this->addReference('entity-Comfanorte-ccf', $EntityCcfComfanorte);
        $this->addReference('entity-Cajasan-ccf', $EntityCcfCajasan);
        $this->addReference('entity-Combarranquilla-ccf', $EntityCcfCombarranquilla);
        $this->addReference('entity-Cafasur-ccf', $EntityCcfCafasur);
        $this->addReference('entity-Comfatolima-ccf', $EntityCcfComfatolima);
        $this->addReference('entity-Comfacasanare-ccf', $EntityCcfComfacasanare);
        $this->addReference('entity-Comfaguajira-ccf', $EntityCcfComfaguajira);
        $this->addReference('entity-Comcaja-ccf', $EntityCcfComcaja);
        $this->addReference('entity-ComfamiliarHuila-ccf', $EntityCcfComfamiliarHuila);
        $this->addReference('entity-ComfamiliarPutumayo-ccf', $EntityCcfComfamiliarPutumayo);
        $this->addReference('entity-Comfaoriente-ccf', $EntityCcfComfaoriente);
        $this->addReference('entity-Comfasucre-ccf', $EntityCcfComfasucre);
        $this->addReference('entity-Comfenalco-ccf', $EntityCcfComfenalco);
        $this->addReference('entity-Fosyga-eps', $EntityEpsFosyga);
        $this->addReference('entity-ComfenalcoValle-eps', $EntityEpsComfenalcoValle);
        $this->addReference('entity-Compensar-eps', $EntityEpsCompensar);
        $this->addReference('entity-Coomeva-eps', $EntityEpsCoomeva);
        $this->addReference('entity-Famisanar-eps', $EntityEpsFamisanar);
        $this->addReference('entity-NuevaEps-eps', $EntityEpsNuevaEps);
        $this->addReference('entity-Aliansalud-eps', $EntityEpsAliansalud);
        $this->addReference('entity-Sanitas-eps', $EntityEpsSanitas);
        $this->addReference('entity-SOS-eps', $EntityEpsSOS);
        $this->addReference('entity-Sura-eps', $EntityEpsSura);
        $this->addReference('entity-CafesaludLiqSaludcop-eps', $EntityEpsCafesaludLiqSaludcop);
        $this->addReference('entity-Saludtototal-eps', $EntityEpsSaludtototal);
        $this->addReference('entity-Saludvida-eps', $EntityEpsSaludvida);
        $this->addReference('entity-SaludColpatria-eps', $EntityEpsSaludColpatria);
        $this->addReference('entity-Cruzblanca-eps', $EntityEpsCruzblanca);
        $this->addReference('entity-MultimedicasSaludConCalidadEpsSA-eps', $EntityEpsMultimedicasSaludConCalidadEpsSA);
        $this->addReference('entity-GoldenGroupEps-eps', $EntityEpsGoldenGroupEps);
        $this->addReference('entity-SaviaSalud-eps', $EntityEpsSaviaSalud);
        $this->addReference('entity-Colsubsidio-eps', $EntityEpsColsubsidio);
        $this->addReference('entity-Comfacor-eps', $EntityEpsComfacor);
        $this->addReference('entity-Cafam-eps', $EntityEpsCafam);
        $this->addReference('entity-Comfachoco-eps', $EntityEpsComfachoco);
        $this->addReference('entity-ComfamiliarHuila-eps', $EntityEpsComfamiliarHuila);
        $this->addReference('entity-ComfamiliarNarino-eps', $EntityEpsComfamiliarNarino);
        $this->addReference('entity-Comfacundi-eps', $EntityEpsComfacundi);
        $this->addReference('entity-CajacopiAtlantico-eps', $EntityEpsCajacopiAtlantico);
        $this->addReference('entity-Caprecom-eps', $EntityEpsCaprecom);
        $this->addReference('entity-Convida-eps', $EntityEpsConvida);
        $this->addReference('entity-Capresoca-eps', $EntityEpsCapresoca);
        $this->addReference('entity-CapitalSalud-eps', $EntityEpsCapitalSalud);
        $this->addReference('entity-Manexka-eps', $EntityEpsManexka);
        $this->addReference('entity-AnasWayuu-eps', $EntityEpsAnasWayuu);
        $this->addReference('entity-Mallamas-eps', $EntityEpsMallamas);
        $this->addReference('entity-Pijaosalud-eps', $EntityEpsPijaosalud);
        $this->addReference('entity-Emdisalud-eps', $EntityEpsEmdisalud);
        $this->addReference('entity-MutualSer-eps', $EntityEpsMutualSer);
        $this->addReference('entity-Emssanar-eps', $EntityEpsEmssanar);
        $this->addReference('entity-Coosalud-eps', $EntityEpsCoosalud);
        $this->addReference('entity-Comparta-eps', $EntityEpsComparta);
        $this->addReference('entity-AsmetSalud-eps', $EntityEpsAsmetSalud);
        $this->addReference('entity-Ambuq-eps', $EntityEpsAmbuq);
        $this->addReference('entity-Ecoopsos-eps', $EntityEpsEcoopsos);
        $this->addReference('entity-SaviaSalud-ars', $EntityArsSaviaSalud);
        $this->addReference('entity-Colsubsidio-ars', $EntityArsColsubsidio);
        $this->addReference('entity-Comfacor-ars', $EntityArsComfacor);
        $this->addReference('entity-Cafam-ars', $EntityArsCafam);
        $this->addReference('entity-Comfachoco-ars', $EntityArsComfachoco);
        $this->addReference('entity-ComfamiliarHuila-ars', $EntityArsComfamiliarHuila);
        $this->addReference('entity-ComfamiliarNarino-ars', $EntityArsComfamiliarNarino);
        $this->addReference('entity-Comfacundi-ars', $EntityArsComfacundi);
        $this->addReference('entity-CajacopiAtlantico-ars', $EntityArsCajacopiAtlantico);
        $this->addReference('entity-Caprecom-ars', $EntityArsCaprecom);
        $this->addReference('entity-Convida-ars', $EntityArsConvida);
        $this->addReference('entity-Capresoca-ars', $EntityArsCapresoca);
        $this->addReference('entity-CapitalSalud-ars', $EntityArsCapitalSalud);
        $this->addReference('entity-Manexka-ars', $EntityArsManexka);
        $this->addReference('entity-AnasWayuu-ars', $EntityArsAnasWayuu);
        $this->addReference('entity-Mallamas-ars', $EntityArsMallamas);
        $this->addReference('entity-Pijaosalud-ars', $EntityArsPijaosalud);
        $this->addReference('entity-Emdisalud-ars', $EntityArsEmdisalud);
        $this->addReference('entity-MutualSer-ars', $EntityArsMutualSer);
        $this->addReference('entity-Emssanar-ars', $EntityArsEmssanar);
        $this->addReference('entity-Coosalud-ars', $EntityArsCoosalud);
        $this->addReference('entity-Comparta-ars', $EntityArsComparta);
        $this->addReference('entity-AsmetSalud-ars', $EntityArsAsmetSalud);
        $this->addReference('entity-Ambuq-ars', $EntityArsAmbuq);
        $this->addReference('entity-Ecoopsos-ars', $EntityArsEcoopsos);

    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}
