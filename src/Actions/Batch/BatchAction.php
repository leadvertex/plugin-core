<?php
/**
 * Created for plugin-core
 * Date: 27.11.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Core\Actions\Batch;


use Leadvertex\Plugin\Components\Batch\Batch;
use Leadvertex\Plugin\Components\Batch\BatchContainer;
use Leadvertex\Plugin\Core\Actions\ActionInterface;
use Slim\Http\Response;

abstract class BatchAction implements ActionInterface
{

    protected function guard(int $number, Response $response): ?Response
    {
        if ($number < 1 || $number > 10) {
            return $response->withStatus(400);
        }

        /** @var Batch|null $batch */
        $batch = Batch::find();
        if (is_null($batch)) {
            return $response->withStatus(425);
        }

        if ($number > 1 && is_null($batch->getOptions($number - 1))) {
            return $response->withStatus(425);
        }

        if (is_null(BatchContainer::getForm($number))) {
            return $response->withStatus(404);
        }

        return null;
    }

}