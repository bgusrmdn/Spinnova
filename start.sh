#!/bin/bash

echo "🎰 Starting SlotMania Development Server..."
echo "================================================"

# Build CSS
echo "Building Tailwind CSS..."
npx tailwindcss -i ./src/input.css -o ./dist/output.css

# Check if live-server is available
if command -v npx &> /dev/null; then
    echo "Starting live server on http://localhost:3000"
    echo "Press Ctrl+C to stop the server"
    echo "================================================"
    npx live-server --port=3000 --host=0.0.0.0 --no-browser
else
    echo "Please install live-server globally: npm install -g live-server"
    echo "Or run: npx live-server --port=3000"
fi