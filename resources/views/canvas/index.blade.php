<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive Canvas - Office Layout Tool</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
        }

        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1em;
        }

        .content {
            padding: 30px;
        }

        .controls {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            align-items: center;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #495057;
            border: 2px solid #e9ecef;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 107, 107, 0.3);
        }

        .canvas-container {
            position: relative;
            border: 3px solid #e9ecef;
            border-radius: 10px;
            background: #f8f9fa;
            margin: 0 auto;
            width: 800px;
            height: 800px;
            overflow: hidden;
        }

        #canvas {
            width: 800px;
            height: 800px;
            cursor: crosshair;
            background: white;
        }

        .desk {
            position: absolute;
            width: 200px;
            height: 120px;
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            border: 2px solid #2d3436;
            border-radius: 8px;
            cursor: move;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            user-select: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .desk:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .desk.selected {
            border: 3px solid #00b894;
            box-shadow: 0 0 0 3px rgba(0, 184, 148, 0.3);
        }

        .desk.rotated {
            transform: rotate(90deg);
        }

        .info-panel {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid #667eea;
        }

        .info-panel h3 {
            margin: 0 0 15px 0;
            color: #495057;
        }

        .info-panel ul {
            margin: 0;
            padding-left: 20px;
            color: #6c757d;
        }

        .info-panel li {
            margin-bottom: 8px;
        }

        .coordinates {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎨 Interactive Canvas</h1>
            <p>Create and arrange office layouts with interactive desks</p>
        </div>

        <div class="content">
            <div class="controls">
                <button class="btn btn-primary" onclick="addDesk()">
                    ➕ Add Desk
                </button>
                <button class="btn btn-secondary" onclick="rotateSelectedDesk()" id="rotateBtn" disabled>
                    🔄 Rotate 90°
                </button>
                <button class="btn btn-danger" onclick="deleteSelectedDesk()" id="deleteBtn" disabled>
                    🗑️ Delete Desk
                </button>
                <button class="btn btn-secondary" onclick="clearCanvas()">
                    🧹 Clear All
                </button>
            </div>

            <div class="canvas-container">
                <canvas id="canvas" width="800" height="800"></canvas>
                <div class="coordinates" id="coordinates">X: 0, Y: 0</div>
            </div>

            <div class="info-panel">
                <h3>🎯 How to Use:</h3>
                <ul>
                    <li><strong>Add Desk:</strong> Click "Add Desk" to place a new 200x120 pixel desk on the canvas</li>
                    <li><strong>Move:</strong> Click and drag any desk to move it around the canvas</li>
                    <li><strong>Select:</strong> Click on a desk to select it (highlighted with green border)</li>
                    <li><strong>Rotate:</strong> Select a desk and click "Rotate 90°" to rotate it</li>
                    <li><strong>Delete:</strong> Select a desk and click "Delete Desk" to remove it</li>
                    <li><strong>Coordinates:</strong> Mouse position is shown in the top-right corner</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        let desks = [];
        let selectedDesk = null;
        let isDragging = false;
        let dragOffset = { x: 0, y: 0 };
        let deskCounter = 1;

        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const coordinates = document.getElementById('coordinates');
        const rotateBtn = document.getElementById('rotateBtn');
        const deleteBtn = document.getElementById('deleteBtn');

        // Initialize canvas
        function initCanvas() {
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, 800, 800);
            
            // Draw grid
            ctx.strokeStyle = '#f0f0f0';
            ctx.lineWidth = 1;
            
            for (let x = 0; x <= 800; x += 50) {
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, 800);
                ctx.stroke();
            }
            
            for (let y = 0; y <= 800; y += 50) {
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(800, y);
                ctx.stroke();
            }
        }

        // Desk class
        class Desk {
            constructor(x, y, id) {
                this.x = x;
                this.y = y;
                this.id = id;
                this.width = 200;
                this.height = 120;
                this.rotation = 0;
                this.isSelected = false;
            }

            draw() {
                ctx.save();
                ctx.translate(this.x + this.width / 2, this.y + this.height / 2);
                ctx.rotate(this.rotation * Math.PI / 180);
                
                // Draw desk rectangle
                ctx.fillStyle = this.isSelected ? '#00b894' : '#74b9ff';
                ctx.fillRect(-this.width / 2, -this.height / 2, this.width, this.height);
                
                // Draw border
                ctx.strokeStyle = this.isSelected ? '#00b894' : '#2d3436';
                ctx.lineWidth = this.isSelected ? 3 : 2;
                ctx.strokeRect(-this.width / 2, -this.height / 2, this.width, this.height);
                
                // Draw text
                ctx.fillStyle = 'white';
                ctx.font = 'bold 14px Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(`Desk ${this.id}`, 0, 0);
                
                ctx.restore();
            }

            containsPoint(x, y) {
                // Transform point to desk's coordinate system
                const centerX = this.x + this.width / 2;
                const centerY = this.y + this.height / 2;
                
                const cos = Math.cos(-this.rotation * Math.PI / 180);
                const sin = Math.sin(-this.rotation * Math.PI / 180);
                
                const dx = x - centerX;
                const dy = y - centerY;
                
                const rotatedX = dx * cos - dy * sin;
                const rotatedY = dx * sin + dy * cos;
                
                return rotatedX >= -this.width / 2 && 
                       rotatedX <= this.width / 2 && 
                       rotatedY >= -this.height / 2 && 
                       rotatedY <= this.height / 2;
            }
        }

        // Add a new desk
        function addDesk() {
            const desk = new Desk(50, 50, deskCounter++);
            desks.push(desk);
            redrawCanvas();
        }

        // Rotate selected desk
        function rotateSelectedDesk() {
            if (selectedDesk) {
                selectedDesk.rotation = (selectedDesk.rotation + 90) % 360;
                redrawCanvas();
            }
        }

        // Delete selected desk
        function deleteSelectedDesk() {
            if (selectedDesk) {
                const index = desks.indexOf(selectedDesk);
                if (index > -1) {
                    desks.splice(index, 1);
                    selectedDesk = null;
                    updateButtons();
                    redrawCanvas();
                }
            }
        }

        // Clear all desks
        function clearCanvas() {
            desks = [];
            selectedDesk = null;
            deskCounter = 1;
            updateButtons();
            redrawCanvas();
        }

        // Update button states
        function updateButtons() {
            rotateBtn.disabled = !selectedDesk;
            deleteBtn.disabled = !selectedDesk;
        }

        // Redraw the entire canvas
        function redrawCanvas() {
            initCanvas();
            desks.forEach(desk => desk.draw());
        }

        // Mouse event handlers
        canvas.addEventListener('mousemove', (e) => {
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            coordinates.textContent = `X: ${Math.round(x)}, Y: ${Math.round(y)}`;
            
            if (isDragging && selectedDesk) {
                selectedDesk.x = x - dragOffset.x;
                selectedDesk.y = y - dragOffset.y;
                
                // Keep desk within canvas bounds
                selectedDesk.x = Math.max(0, Math.min(800 - selectedDesk.width, selectedDesk.x));
                selectedDesk.y = Math.max(0, Math.min(800 - selectedDesk.height, selectedDesk.y));
                
                redrawCanvas();
            }
        });

        canvas.addEventListener('mousedown', (e) => {
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Check if clicking on a desk
            let clickedDesk = null;
            for (let i = desks.length - 1; i >= 0; i--) {
                if (desks[i].containsPoint(x, y)) {
                    clickedDesk = desks[i];
                    break;
                }
            }
            
            // Deselect all desks
            desks.forEach(desk => desk.isSelected = false);
            
            if (clickedDesk) {
                selectedDesk = clickedDesk;
                selectedDesk.isSelected = true;
                isDragging = true;
                
                // Calculate drag offset
                dragOffset.x = x - selectedDesk.x;
                dragOffset.y = y - selectedDesk.y;
            } else {
                selectedDesk = null;
                isDragging = false;
            }
            
            updateButtons();
            redrawCanvas();
        });

        canvas.addEventListener('mouseup', () => {
            isDragging = false;
        });

        canvas.addEventListener('mouseleave', () => {
            isDragging = false;
        });

        // Initialize the canvas on load
        window.addEventListener('load', () => {
            initCanvas();
            updateButtons();
        });
    </script>
</body>
</html> 