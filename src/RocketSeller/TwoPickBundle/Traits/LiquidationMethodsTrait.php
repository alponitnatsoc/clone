<?php

namespace RocketSeller\TwoPickBundle\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use RocketSeller\TwoPickBundle\Entity\Novelty;

trait LiquidationMethodsTrait
{

    protected function liquidationDetail($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Liquidation');
        $liquidation = $repository->find($id);

        return $liquidation;
    }

    protected function totalLiquidation($data)
    {
        $total = $totalDed = $totalDev = 0;
        $novelties=new ArrayCollection();
        foreach ($data as $key => $info) {
        	if(!is_array($info)){
        		continue;
	        }
            $payroll_code = isset($info["CON_CODIGO"]) ? $info["CON_CODIGO"] : false;
            /** @var \RocketSeller\TwoPickBundle\Entity\NoveltyType $noveltyType */
            $noveltyType = $this->noveltyTypeByPayrollCode($payroll_code);
            if ($noveltyType) {
                $novelty=new Novelty();
                $novelty->setNoveltyTypeNoveltyType($noveltyType);
                $novelty->setName($noveltyType->getName());
                $novelty->setSqlValue($info["NOMI_VALOR_LOCAL"]);
                $novelty->setSqlNovConsec($info["NOV_CONSEC"]);
                $novelty->setUnits($info["NOMI_UNIDADES"]);
                $novelties->add($novelty);
                //                 var_dump($info["NOMI_VALOR"] . " - " . $noveltyType->getNaturaleza());
                switch ($noveltyType->getNaturaleza()):
                    case "DED":
                        $total -= $info["NOMI_VALOR_LOCAL"];
                        $totalDed += $info["NOMI_VALOR_LOCAL"];
                        break;
                    case "DEV":
                        $total += $info["NOMI_VALOR_LOCAL"];
                        $totalDev += $info["NOMI_VALOR_LOCAL"];
                        break;
                    default:
                        break;
                endswitch;
            }
        }
        return array(
            "total" => ceil($total),
            "totalDed" => ceil($totalDed),
            "totalDev" => ceil($totalDev),
            'novelties'=> $novelties
        );
    }

    /**
     * Obtener las liquidaciones de una relacion employer has employee y que sea de determinado tipo
     *
     * @param integer $id - Id de la relacion employer_has_employee
     * @param integer $liquidationType
     * @param integer $contract - Id del contrato
     */
    protected function liquidationByTypeAndEmHEmAndContract($id, $liquidationType, $contract)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Liquidation');
        $liquidation = $repository->findOneBy(array(
            'employerHasEmployee' => $id,
            'liquidationType' => $liquidationType,
            'contract' => $contract
        ));

        return $liquidation;
    }
}
