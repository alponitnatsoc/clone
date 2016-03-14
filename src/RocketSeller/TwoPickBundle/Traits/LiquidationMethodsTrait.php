<?php

namespace RocketSeller\TwoPickBundle\Traits;

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

        foreach ($data as $key => $info) {
            $payroll_code = isset($info["CON_CODIGO"]) ? $info["CON_CODIGO"] : false;
            /** @var \RocketSeller\TwoPickBundle\Entity\NoveltyType $noveltyType */
            $noveltyType = $this->noveltyTypeByPayrollCode($payroll_code);
            if ($noveltyType) {
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
            "total" => floor($total),
            "totalDed" => floor($totalDed),
            "totalDev" => floor($totalDev)
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
