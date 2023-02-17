<?php declare(strict_types = 1);

namespace Dogma\Tests\Math;

use Dogma\Math\Sequence\Fibonacci;
use Dogma\Math\Sequence\Lucas;
use Dogma\Math\Sequence\Prime;
use Dogma\Math\Sequence\Tribonacci;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


lucas:
Assert::same(Lucas::getNth(0), 2);
Assert::same(Lucas::getNth(1), 1);
Assert::same(Lucas::getNth(2), 3);
Assert::same(Lucas::getNth(3), 4);
Assert::same(Lucas::getNth(4), 7);
Assert::same(Lucas::getNth(5), 11);
Assert::same(Lucas::getNth(6), 18);
Assert::same(Lucas::getNth(7), 29);
Assert::same(Lucas::getNth(8), 47);
Assert::same(Lucas::getNth(9), 76);


fibonacci:
Assert::same(Fibonacci::getNth(1), 1);
Assert::same(Fibonacci::getNth(2), 1);
Assert::same(Fibonacci::getNth(3), 2);
Assert::same(Fibonacci::getNth(4), 3);
Assert::same(Fibonacci::getNth(5), 5);
Assert::same(Fibonacci::getNth(6), 8);
Assert::same(Fibonacci::getNth(7), 13);
Assert::same(Fibonacci::getNth(8), 21);
Assert::same(Fibonacci::getNth(9), 34);
Assert::same(Fibonacci::getNth(10), 55);
Assert::same(Fibonacci::getNth(11), 89);


tribonacci:
Assert::same(Tribonacci::getNth(1), 1);
Assert::same(Tribonacci::getNth(2), 1);
Assert::same(Tribonacci::getNth(3), 2);
Assert::same(Tribonacci::getNth(4), 4);
Assert::same(Tribonacci::getNth(5), 7);
Assert::same(Tribonacci::getNth(6), 13);
Assert::same(Tribonacci::getNth(7), 24);
Assert::same(Tribonacci::getNth(8), 44);
Assert::same(Tribonacci::getNth(9), 81);


prime:
Assert::same(Prime::getBetween(8192, 10000), [8209, 8219, 8221, 8231, 8233, 8237, 8243, 8263, 8269, 8273, 8287, 8291, 8293, 8297, 8311, 8317, 8329, 8353, 8363, 8369, 8377, 8387, 8389, 8419, 8423, 8429, 8431, 8443, 8447, 8461, 8467, 8501, 8513, 8521, 8527, 8537, 8539, 8543, 8563, 8573, 8581, 8597, 8599, 8609, 8623, 8627, 8629, 8641, 8647, 8663, 8669, 8677, 8681, 8689, 8693, 8699, 8707, 8713, 8719, 8731, 8737, 8741, 8747, 8753, 8761, 8779, 8783, 8803, 8807, 8819, 8821, 8831, 8837, 8839, 8849, 8861, 8863, 8867, 8887, 8893, 8923, 8929, 8933, 8941, 8951, 8963, 8969, 8971, 8999, 9001, 9007, 9011, 9013, 9029, 9041, 9043, 9049, 9059, 9067, 9091, 9103, 9109, 9127, 9133, 9137, 9151, 9157, 9161, 9173, 9181, 9187, 9199, 9203, 9209, 9221, 9227, 9239, 9241, 9257, 9277, 9281, 9283, 9293, 9311, 9319, 9323, 9337, 9341, 9343, 9349, 9371, 9377, 9391, 9397, 9403, 9413, 9419, 9421, 9431, 9433, 9437, 9439, 9461, 9463, 9467, 9473, 9479, 9491, 9497, 9511, 9521, 9533, 9539, 9547, 9551, 9587, 9601, 9613, 9619, 9623, 9629, 9631, 9643, 9649, 9661, 9677, 9679, 9689, 9697, 9719, 9721, 9733, 9739, 9743, 9749, 9767, 9769, 9781, 9787, 9791, 9803, 9811, 9817, 9829, 9833, 9839, 9851, 9857, 9859, 9871, 9883, 9887, 9901, 9907, 9923, 9929, 9931, 9941, 9949, 9967, 9973]);
