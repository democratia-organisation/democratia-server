const { execSync } = require('child_process');

try {
  console.log('🚀 Running Pint inside Docker...');
  // On exécute la commande SANS lui passer les arguments de lint-staged
  execSync('docker exec -i -e XDEBUG_MODE=off democratia-web-1 vendor/bin/pint /usr/src/server', { stdio: 'inherit' });
} catch (error) {
  // Si Pint échoue (code 1), on peut choisir de bloquer le commit ou non
  // Pour l'instant on laisse passer pour que Pint puisse corriger les fichiers
  process.exit(0); 
}