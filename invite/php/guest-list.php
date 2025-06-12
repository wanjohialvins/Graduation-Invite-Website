<?php
function normalize($name) {
    return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
}
function expandNames($list) {
    $names = [];
    foreach ($list as $entry) {
        $split = preg_split('/\s*&\s*/', $entry);
        foreach ($split as $name) {
            $clean = trim($name);
            if ($clean !== '') $names[] = strtolower($clean);
        }
    }
    return $names;
}

$rsvpRaw = $plusRaw = $manualRaw = [];
if (file_exists('rsvp_log.txt')) {
    foreach (file('rsvp_log.txt') as $line) {
        if (stripos($line, '✅ rsvp:') !== false) {
            $rsvpRaw[] = trim(explode('|', str_ireplace('✅ rsvp:', '', $line))[0]);
        } elseif (stripos($line, 'plus one:') !== false) {
            $plusRaw[] = trim(explode('|', str_ireplace('plus one:', '', $line))[0]);
        }
    }
}
if (file_exists('guest_names.txt')) {
    $plusRaw = array_merge($plusRaw, array_map('trim', file('guest_names.txt')));
}
if (file_exists('manual_adds.txt')) {
    $manualRaw = array_merge($manualRaw, array_map('trim', file('manual_adds.txt')));
}

$expandedRSVP = expandNames(array_merge($rsvpRaw, $manualRaw));
$expandedPlus = expandNames($plusRaw);
$normSet = [];
$finalRsvp = [];
$finalPlus = [];

foreach ($expandedRSVP as $name) {
    $norm = normalize($name);
    if (!isset($normSet[$norm])) {
        $normSet[$norm] = true;
        $finalRsvp[] = $name;
    }
}
foreach ($expandedPlus as $name) {
    $norm = normalize($name);
    if (!isset($normSet[$norm])) {
        $normSet[$norm] = true;
        $finalPlus[] = $name;
    }
}

usort($finalRsvp, fn($a, $b) => strlen($b) - strlen($a));
usort($finalPlus, fn($a, $b) => strlen($b) - strlen($a));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="grad.png" />
  <title>Guest List</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap');
    body {
      margin: 0;
      padding: 16px;
      background: radial-gradient(circle, #0c0f1c 0%, #050810 100%);
      font-family: 'Share Tech Mono', monospace;
      color: #00ffe0;
      text-align: center;
    }
    h1 {
      font-size: clamp(1.5rem, 5vw, 2rem);
      margin-bottom: 8px;
      text-shadow: 0 0 8px #00ffe0;
    }
    .count {
      font-size: 0.9rem;
      opacity: 0.7;
      margin-bottom: 16px;
    }
    .group-label {
      margin-top: 24px;
      font-size: clamp(1rem, 4vw, 1.2rem);
      color: #aef;
      text-shadow: 0 0 6px #00c9c9;
    }
    .terminal {
      max-width: 100%;
      margin: 0 auto;
      padding: 16px 12px;
      border: 1px solid rgba(0, 255, 200, 0.2);
      background: rgba(0, 255, 200, 0.02);
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 255, 200, 0.05);
      overflow-x: auto;
    }
    .name-line {
      font-size: clamp(0.9rem, 4vw, 1.1rem);
      white-space: pre;
      overflow: hidden;
      display: inline-block;
      width: 100%;
      text-align: left;
      margin: 4px 0;
      opacity: 0;
      animation: fadeIn 0.5s ease forwards;
    }
    @keyframes fadeIn {
      to { opacity: 1; }
    }
    .cursor {
      display: inline-block;
      width: 0.6ch;
      background-color: #00ffe0;
      animation: blink 0.8s steps(1) infinite;
    }
    @keyframes blink {
      0%, 100% { opacity: 0; }
      50% { opacity: 1; }
    }
    button {
      padding: 12px 18px;
      margin: 10px 6px;
      font-size: 1rem;
      font-weight: bold;
      border: none;
      border-radius: 6px;
      color: #011010;
      background: linear-gradient(135deg, #00ffe0, #00b7ff);
      cursor: pointer;
      box-shadow: 0 4px 8px rgba(0, 255, 231, 0.2);
      transition: all 0.2s ease;
      width: auto;
      max-width: 90vw;
    }
    button:hover {
      transform: scale(1.05);
    }
    #csvBtn {
      display: none;
    }
  </style>
</head>
<body>
  <h1>🧾 Those Confirmed Attending</h1>
  <div class="count"><?= count($finalRsvp) ?> rsvp’d · <?= count($finalPlus) ?> plus ones</div>

  <button id="startBtn" onclick="startTyping()">👁 view list</button>

  <div class="terminal" id="guestList" style="display:none;">
    <div class="group-label">🎓 rsvp responses</div>
    <div id="rsvpGroup"></div>
    <div class="group-label">➕ plus ones</div>
    <div id="plusOneGroup"></div>
  </div>

  <button id="csvBtn" onclick="exportToCSV()">📥 export as csv</button>

  <audio id="typeSound" preload="auto">
    <source src="mech.mp3" type="audio/mpeg" />
  </audio>

  <script>
    const rsvps = <?= json_encode($finalRsvp) ?>;
    const plusOnes = <?= json_encode($finalPlus) ?>;
    const typeSound = document.getElementById('typeSound');
    const rsvpGroup = document.getElementById('rsvpGroup');
    const plusOneGroup = document.getElementById('plusOneGroup');
    const guestList = document.getElementById('guestList');
    const csvBtn = document.getElementById('csvBtn');

    let started = false;
    let allRendered = [];

    function playTypedSound() {
      if (typeSound.readyState >= 2) {
        try {
          typeSound.pause();
          typeSound.currentTime = 0;
          typeSound.playbackRate = 0.85 + Math.random() * 0.3;
          typeSound.volume = 0.4;
          typeSound.play().catch(() => {});
        } catch (e) {}
      }
    }

    function typeName(name, container, delay) {
      const line = document.createElement('div');
      line.className = 'name-line';
      const cursor = document.createElement('span');
      cursor.className = 'cursor';
      line.appendChild(cursor);
      container.appendChild(line);

      let i = 0;
      function typeChar() {
        if (i < name.length) {
          cursor.insertAdjacentText('beforebegin', name[i]);
          playTypedSound();
          i++;
          setTimeout(typeChar, 90 + Math.random() * 60);
        } else {
          cursor.remove();
          allRendered.push({ line, group: container });
          if (allRendered.length === (rsvps.length + plusOnes.length)) reorder();
        }
      }
      setTimeout(typeChar, delay);
    }

    function reorder() {
      setTimeout(() => {
        rsvpGroup.innerHTML = '';
        plusOneGroup.innerHTML = '';
        const sortedR = allRendered.filter(e => e.group === rsvpGroup).sort((a, b) => b.line.textContent.length - a.line.textContent.length);
        const sortedP = allRendered.filter(e => e.group === plusOneGroup).sort((a, b) => b.line.textContent.length - a.line.textContent.length);
        sortedR.forEach(e => rsvpGroup.appendChild(e.line));
        sortedP.forEach(e => plusOneGroup.appendChild(e.line));
        csvBtn.style.display = 'inline-block';
      }, 1000);
    }

    function exportToCSV() {
      const lines = ['RSVPs', ...rsvps, '', 'Plus Ones', ...plusOnes];
      const blob = new Blob([lines.join('\n')], { type: 'text/csv' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'guest_names.csv';
      a.click();
    }

    function startTyping() {
      if (started) return;
      started = true;
      guestList.style.display = 'block';

      let delay = 0;
      rsvps.forEach(name => {
        typeName(name, rsvpGroup, delay);
        delay += name.length * 110 + 200;
      });
      plusOnes.forEach(name => {
        typeName(name, plusOneGroup, delay);
        delay += name.length * 110 + 200;
      });
    }
  </script>
</body>
</html>
