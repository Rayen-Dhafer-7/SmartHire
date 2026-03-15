name: SmartHire CI

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  laravel-tests:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout repository
        uses: actions/checkout@v4

      - name: Set up Docker Buildx
        uses: docker/setup-buildx-action@v3

      # --- Create certificate file from secret ---
      - name: Create certificate file
        run: |
          # Create certs directory
          mkdir -p certs
          
          # Write the certificate CONTENT from secret to file
          echo "${{ secrets.DB_SSL_CA }}" > certs/ca.pem
          
          # Set proper permissions
          chmod 644 certs/ca.pem
          
          echo "Certificate created at certs/ca.pem"
          ls -la certs/

      # --- Prepare env files for CI ---
      - name: Prepare env files for CI
        run: |
          # Laravel .env
          cp laravel/.env.example laravel/.env
          
          # Update Laravel .env with database settings
          echo "DB_CONNECTION=mysql" >> laravel/.env
          echo "DB_HOST=${{ secrets.DB_HOST }}" >> laravel/.env
          echo "DB_PORT=${{ secrets.DB_PORT }}" >> laravel/.env
          echo "DB_DATABASE=${{ secrets.DB_DATABASE }}" >> laravel/.env
          echo "DB_USERNAME=${{ secrets.DB_USERNAME }}" >> laravel/.env
          echo "DB_PASSWORD=${{ secrets.DB_PASSWORD }}" >> laravel/.env
          
          # Set DB_SSL_CA to the FILE PATH (not the content)
          echo "DB_SSL_CA=certs/ca.pem" >> laravel/.env
          
          # Vue .env
          echo "VITE_API_URL=http://placeholder:8000" > vue/.env
          echo "FRONTEND_URL=http://placeholder:5174" >> vue/.env

      - name: Install Vue node_modules for CI
        run: |
          cd vue
          npm install --include=dev
          npm install vite@latest --save-dev

      - name: Start services with Docker Compose
        run: |
          docker compose up -d --build --wait
          sleep 10

      - name: Show container status
        run: docker compose ps -a

      # Verify certificate is in the container
      - name: Verify certificate in container
        run: |
          echo "Checking certificate in container:"
          docker exec smarthire_laravel ls -la /var/www/html/certs/ || true
          
          # Fix storage permissions
          docker exec smarthire_laravel chmod -R 777 storage bootstrap/cache

      # Host IP detection
      - name: Set Vue .env and Behat base_url to host IP
        run: |
          HOST_IP=$(docker exec smarthire_selenium ip route show default 2>/dev/null | awk '/default/ {print $3}' | tr -d '[:space:]' || echo "172.17.0.1")
          if [ -z "$HOST_IP" ]; then
            HOST_IP="172.17.0.1"
            echo "Using fallback host IP: $HOST_IP"
          else
            echo "Detected host gateway IP: $HOST_IP"
          fi
          
          # Update Vue .env
          echo "VITE_API_URL=http://${HOST_IP}:8000" > vue/.env
          echo "FRONTEND_URL=http://${HOST_IP}:5174" >> vue/.env
          
          # Update behat.yml
          if [ -f laravel/behat.yml ]; then
            sed -i "s|base_url:.*|base_url: http://${HOST_IP}:5174|" laravel/behat.yml
          fi

      - name: Restart Vue to pick up .env
        run: docker compose restart vue

      - name: Prepare Laravel
        run: |
          docker exec smarthire_laravel php artisan key:generate --force
          docker exec smarthire_laravel php artisan config:clear
          docker exec smarthire_laravel php artisan config:cache
          docker exec smarthire_laravel php artisan migrate --force

      - name: Wait for Laravel to respond
        run: |
          echo "Waiting for Laravel..."
          code="000"
          for i in $(seq 1 24); do
            code=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/ || true)
            if [ "$code" != "000" ]; then
              echo "Laravel responded HTTP $code"
              break
            fi
            sleep 5
          done
          if [ "$code" = "000" ]; then
            echo "Laravel logs:"
            docker compose logs laravel --tail=80
            exit 1
          fi

      - name: Wait for Vue dev server
        run: |
          echo "Polling Vue..."
          code="000"
          for i in $(seq 1 72); do
            code=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:5174/ || true)
            if [ "$code" != "000" ]; then
              echo "Vue responded HTTP $code"
              break
            fi
            sleep 5
          done
          if [ "$code" = "000" ]; then
            echo "Vue logs:"
            docker compose logs vue --tail=200
            exit 1
          fi

      # Test Aiven MySQL connection + latency
      - name: Test Aiven MySQL connection speed
        run: |
          echo "Testing Aiven MySQL connection..."
          echo "Certificate in container:"
          docker exec smarthire_laravel ls -la /var/www/html/certs/
          echo ""
          echo "Calling your debug-speed endpoint:"
          curl -v http://localhost:8000/debug-speed || true
          
          # If curl fails, show logs
          if [ $? -ne 0 ]; then
            echo ""
            echo "Laravel logs:"
            docker compose logs laravel --tail=50
          fi

      # Warm SPA
      - name: Warm Vue app
        run: |
          curl -s -o /dev/null http://localhost:5174/
          curl -s -o /dev/null http://localhost:5174/login
          sleep 15

      - name: Run Behat tests
        run: docker exec smarthire_laravel vendor/bin/behat --format=pretty || true