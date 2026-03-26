const { execSync } = require('child_process');

try {
  console.log('🚀 Running Pint inside Docker...');
  execSync('docker exec -i -e XDEBUG_MODE=off democratia-web-1 vendor/bin/pint /usr/src/server', { stdio: 'inherit' });
} catch (error) {
  process.exit(1); 
}