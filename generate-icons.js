/**
 * Generate PWA icons for Havun Admin
 * Run with: node generate-icons.js
 */

const fs = require('fs');
const { createCanvas } = require('canvas');

function generateIcon(size, filename) {
    const canvas = createCanvas(size, size);
    const ctx = canvas.getContext('2d');

    // Background gradient (Indigo)
    const gradient = ctx.createLinearGradient(0, 0, size, size);
    gradient.addColorStop(0, '#6366F1'); // Indigo-500
    gradient.addColorStop(1, '#4F46E5'); // Indigo-600
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, size, size);

    // Add subtle shadow effect
    ctx.shadowColor = 'rgba(0, 0, 0, 0.3)';
    ctx.shadowBlur = size * 0.02;
    ctx.shadowOffsetX = size * 0.01;
    ctx.shadowOffsetY = size * 0.01;

    // Text "HA"
    ctx.fillStyle = '#FFFFFF';
    ctx.font = `bold ${size * 0.4}px Arial`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('HA', size / 2, size / 2);

    // Reset shadow
    ctx.shadowColor = 'transparent';
    ctx.shadowBlur = 0;
    ctx.shadowOffsetX = 0;
    ctx.shadowOffsetY = 0;

    // Add a subtle border
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
    ctx.lineWidth = size * 0.02;
    ctx.strokeRect(size * 0.05, size * 0.05, size * 0.9, size * 0.9);

    // Save to file
    const buffer = canvas.toBuffer('image/png');
    fs.writeFileSync(filename, buffer);
    console.log(`✅ Generated ${filename}`);
}

// Generate icons
try {
    generateIcon(192, './public/icon-192x192.png');
    generateIcon(512, './public/icon-512x512.png');
    console.log('\n🎉 All icons generated successfully!');
} catch (error) {
    console.error('❌ Error generating icons:', error.message);
    console.log('\n📦 Install canvas package first: npm install canvas');
}
