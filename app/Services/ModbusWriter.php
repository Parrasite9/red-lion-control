<?php

namespace App\Services;

use ModbusTcpClient\Packet\ResponseFactory;
use ModbusTcpClient\Network\BinaryStreamConnection;
use ModbusTcpClient\Packet\ModbusFunction\WriteSingleRegisterRequest;

class ModbusWriter
{
    public function write(int $address, int $value)
    {
        $connection = BinaryStreamConnection::getBuilder()
            ->setHost('127.0.0.1')
            ->setPort(5020)
            ->setTimeoutSec(3)
            ->build();

        $modbusRequest = new WriteSingleRegisterRequest($address, $value, 1);

        try {
            $connection->connect();

            $responseBytes = $connection->sendAndReceive($modbusRequest);

            ResponseFactory::parseResponseOrThrow($responseBytes);

            if ($responseBytes !== (string) $modbusRequest) {
                throw new \RuntimeException('The reply did not match the request.');
            }

            return [
                'address' => $modbusRequest->getStartAddress(),
                'value' => $modbusRequest->getValue(),
                'outcome' => 'Simulator acknowledged the write',
            ];
        } finally {
            $connection->close();
        }
    }
}