<?php
namespace TmlpStats\Tests\Traits;

trait MocksMessages
{
    /**
     * Sets up addMessage method mocks based on input messages array
     *
     * @param     $validator  Mock object instance
     * @param     $messages   Array of messages. Each message is an array of arguments
     * @param int $callOffset Offset of mocked function calls (needed to specify expects at()
     *
     * @return mixed          Returns validator instance with updated expects
     * @throws \Exception
     */
    public function setupMessageMocks($validator, $messages, &$callOffset = 0)
    {
        if (!$messages) {
            $validator->expects($this->never())
                      ->method('addMessage');
            return $validator;
        }

        for ($i = 0; $i < count($messages); $i++) {
            switch (count($messages[$i])) {
                case 5:
                    $validator->expects($this->at($i + $callOffset))
                              ->method('addMessage')
                              ->with($messages[$i][0], $messages[$i][1], $messages[$i][2], $messages[$i][3], $messages[$i][4]);
                    break;
                case 4:
                    $validator->expects($this->at($i + $callOffset))
                              ->method('addMessage')
                              ->with($messages[$i][0], $messages[$i][1], $messages[$i][2], $messages[$i][3]);
                    break;
                case 3:
                    $validator->expects($this->at($i + $callOffset))
                              ->method('addMessage')
                              ->with($messages[$i][0], $messages[$i][1], $messages[$i][2]);
                    break;
                case 2:
                    $validator->expects($this->at($i + $callOffset))
                              ->method('addMessage')
                              ->with($messages[$i][0], $messages[$i][1]);
                    break;
                case 1:
                    $validator->expects($this->at($i + $callOffset))
                              ->method('addMessage')
                              ->with($messages[$i][0]);
                    break;
                default:
                    throw new \Exception('Invalid number of arguments passed to ' . __FUNCTION__);
            }
        }
        $callOffset += $i;

        return $validator;
    }
}
