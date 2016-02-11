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
        $total = 0;
        foreach ($data as $key => $info) {
            $payroll_code = $info["CON_CODIGO"];
            /** @var \RocketSeller\TwoPickBundle\Entity\NoveltyType $noveltyType */
            $noveltyType = $this->noveltyTypeByPayrollCode($payroll_code);
            if ($noveltyType) {
                //                 var_dump($info["NOMI_VALOR"] . " - " . $noveltyType->getNaturaleza());
                switch ($noveltyType->getNaturaleza()):
                    case "DED":
                        $total -= $info["NOMI_VALOR"];
                        break;
                    case "DEV":
                        $total += $info["NOMI_VALOR"];
                        break;
                    default:
                        break;
                endswitch;
            }
        }
        return $total;
    }
}