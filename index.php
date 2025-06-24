<?php
function gcd($a, $b) {
    while ($b != 0) {
        $t = $b;
        $b = $a % $b;
        $a = $t;
    }
    return abs($a);
}

function isRepeatingDecimal($numerator, $denominator) {
    $gcd_val = gcd($numerator, $denominator);
    $simplifiedDenominator = $denominator / $gcd_val;

    while ($simplifiedDenominator % 2 === 0) {
        $simplifiedDenominator /= 2;
    }
    while ($simplifiedDenominator % 5 === 0) {
        $simplifiedDenominator /= 5;
    }

    return $simplifiedDenominator !== 1;
}

$d1 = $_GET["dividend"] ?? 0;
$d2 = $_GET["divisor"] ?? 1;

$quotient = "";
$subtractions = [];
$isRepeating = false;

if ($d2 == 0) {
    $quotient = "Error";
    $remainder = "Error";
} else {
    $wasScaled = false;
    $scaleCount = 0;
    while (fmod($d1, 1) != 0 || fmod($d2, 1) != 0) {
        $d1 *= 10;
        $d2 *= 10;
        $wasScaled = true;
        $scaleCount++;
    }

    $isRepeating = isRepeatingDecimal($d1, $d2);

    $integerPart = intdiv($d1, $d2);
    $integerMultiplication = $integerPart * $d2;
    $remainder = $d1 - $integerMultiplication;

    $subtractions[] = [
        'multiplication' => $integerMultiplication,
        'remainder' => $remainder
    ];

    $decimalQuotient = (string)$integerPart;

    if ($remainder != 0) {
        $decimalQuotient .= ".";
        $seenRemainders = [];
        $digits = [];
        $cycleStartPosition = null;
        $digitCounter = 0;
        $maxDigits = 50;
        $zeroSubtractedAlready = false;

        while ($remainder != 0 && $digitCounter < $maxDigits) {
            if (isset($seenRemainders[$remainder])) {
                $cycleStartPosition = $seenRemainders[$remainder];
                break;
            }

            $seenRemainders[$remainder] = $digitCounter;

            $remainder *= 10;
            $digit = intdiv($remainder, $d2);
            $multiplication = $digit * $d2;
            $remainder -= $multiplication;

            if ($multiplication > 0 || !$zeroSubtractedAlready) {
                $subtractions[] = [
                    'multiplication' => $multiplication,
                    'remainder' => $remainder
                ];
                if ($multiplication == 0) {
                    $zeroSubtractedAlready = true;
                }
            }

            $digits[] = $digit;
            $digitCounter++;
        }

        if ($cycleStartPosition === null) {
            $decimalQuotient .= implode("", $digits);
        } else {
            $nonRepeatingPart = array_slice($digits, 0, $cycleStartPosition);
            $repeatingPart = array_slice($digits, $cycleStartPosition);

            $decimalQuotient .= implode("", $nonRepeatingPart);

            $repeatingTimes = 2;
            for ($i = 0; $i < $repeatingTimes; $i++) {
                foreach ($repeatingPart as $repeatingDigit) {
                    $remainder *= 10;
                    $multiplication = $repeatingDigit * $d2;
                    $remainder -= $multiplication;

                    $subtractions[] = [
                        'multiplication' => $multiplication,
                        'remainder' => $remainder
                    ];

                    $decimalQuotient .= $repeatingDigit;
                }
            }
            foreach($repeatingPart as $repeatingDigit) {
                $decimalQuotient .= $repeatingDigit;
            }
        }
    } else {
        $decimalQuotient = (string)$integerPart;
    }

    $quotient = $decimalQuotient;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Decimal Division</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div id="container">
    <h1>Decimal Division Anatomy</h1>
    <form method="get" action="<?= $_SERVER["PHP_SELF"] ?>">
        <label for="dividend" id="dividendLabel">Dividend:</label><br>
        <input type="number" step="any" id="dividend" name="dividend" value="<?= $d1 ?>" required><br>

        <label for="divisor" id="divisorLabel">Divisor:</label><br>
        <input type="number" step="any" id="divisor" name="divisor" value="<?= $d2 ?>" required><br>

        <button type="submit">Calculate</button>
    </form>
    <?php if ($wasScaled): ?>
        <p style="color: orange; font-weight: bold; margin-top: 10px;">
            Note: The dividend and divisor were multiplied by 10 (<?= $scaleCount ?> time<?= $scaleCount > 1 ? "s" : "" ?>) 
        to eliminate decimal points and allow manual division with whole numbers.
        </p>
    <?php endif; ?>
</div>

<div id="structure-container">
    <h2>Division Steps</h2>
    <div id="calculation-container">
        <div id="dividend-grid" class="operands-container">
            <div></div>
            <div id="dividend-container"><?= $d1 ?></div>
        </div>
        <div id="divisor-grid" class="operands-container">
            <div id="divisor-container" class="operands-container"><?= $d2 ?></div>
        </div>
        <div id="remainder-grid" class="operands-container">
            <div></div>
            <div id="remainder-container">
                <?php
                    $space = -10;
                    $totalSteps = count($subtractions);

                    for ($i = 0; $i < $totalSteps; $i++) {
                        $step = $subtractions[$i];

                        if (isset($remainderBackup) && strlen((string)$step['multiplication']) < strlen((string)$remainderBackup)) {
                            $space += 15;
                        }

                        $width = strlen((string)$step['multiplication']) * 25;
                        echo "<p style='display: none; width: {$width}px; margin-left: {$space}px; margin-top:-30px; border-bottom: 3px solid black;'>-" . $step['multiplication'] . "</p>";

                        $digitLength = strlen((string)abs($step['multiplication']));
                        if($step['multiplication']>0){
                            $space += ($digitLength-strlen((string)$step['remainder'])) * 14.8;
                        }
                        $space+=10.14;
                        if ($i === $totalSteps - 1) {
                            echo "<p style='display: none; margin-left: {$space}px; margin-top:-30px;'>{$step['remainder']}</p>";
                        } else {
                            echo "<p style='display: none; margin-left: {$space}px; margin-top:-30px;'>" . ($step['remainder'] * 10) . "</p>";
                        }

                        $space -= 8;
                        $remainderBackup = $step['remainder'] * 10;
                    }
                ?>
            </div>
        </div>
        <div id="quotient-container" class="operands-container" data-valor="<?= $quotient ?>"></div>
    </div>

    <?php if ($d2 != 0): ?>
    <div id="result-type">
        <?php if ($isRepeating): ?>
            <p style="color: red; font-weight: bold;">
                Warning: this division results in a repeating decimal! The cycle was repeated 3 times.
            </p>
        <?php else: ?>
            <p style="color: green; font-weight: bold;">
                This division has an exact decimal result.
            </p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
<button class="btn-export" onclick="exportImage()">📷 Export as Image</button>

<!-- Script html2canvas (CDN) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
async function animar() {
  const paragrafos = document.querySelectorAll("#remainder-container p");
  const qContainer = document.getElementById("quotient-container");
  const resultadoFinal = qContainer.dataset.valor || "";
  qContainer.textContent = "";

  let quocienteIndex = 0;

  for (let i = 0; i < paragrafos.length; i++) {
    const p = paragrafos[i];
    p.style.display = "block";

    // Se for um subtraendo, mostra o próximo dígito do quociente
    if (p.textContent.trim().startsWith("-") && quocienteIndex < resultadoFinal.length) {
      qContainer.textContent += resultadoFinal[quocienteIndex];
      quocienteIndex++;
    }

    await new Promise(resolve => setTimeout(resolve, 700));
  }

  // Garante que o último dígito (ex: o "5" em 10.5) apareça se faltou
  while (quocienteIndex < resultadoFinal.length) {
    qContainer.textContent += resultadoFinal[quocienteIndex];
    quocienteIndex++;
  }
}

// ✅ Executa assim que o DOM estiver pronto
document.addEventListener("DOMContentLoaded", animar);

function exportImage() {
    const scrollableDiv = document.getElementById('structure-container');
  
    // Clonar o conteúdo inteiro, sem o scroll
    const clone = scrollableDiv.cloneNode(true);
    clone.style.width = scrollableDiv.scrollWidth + 'px';
    clone.style.height = scrollableDiv.scrollHeight + 'px';
    clone.style.overflow = 'visible';
    clone.style.position = 'absolute';
    clone.style.left = '-9999px'; // esconder da tela

    document.body.appendChild(clone);

    html2canvas(clone).then(canvas => {
        const link = document.createElement('a');
        link.download = 'division.png';
        link.href = canvas.toDataURL();
        link.click();
        document.body.removeChild(clone);
    });
}
</script>

</body>
</html>
