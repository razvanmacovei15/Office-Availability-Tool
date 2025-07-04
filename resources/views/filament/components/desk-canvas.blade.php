<div>
    <canvas id="fabricCanvas" width="600" height="400" class="border border-gray-300 rounded"></canvas>

    <button onclick="addRect()" class="mt-2 px-4 py-2 bg-blue-500 text-black rounded">Add Rectangle</button>
    <button onclick="addBlueRect()" class="mt-2 px-4 py-2 bg-blue-500 text-black rounded">Add Blue Rectangle</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = new fabric.Canvas('fabricCanvas');

        window.addRect = function () {
            const rect = new fabric.Rect({
                left: 100,
                top: 100,
                fill: 'red',
                width: 50,
                height: 50,
            });
            canvas.add(rect);
        }
        window.addBlueRect = function () {
            const rect = new fabric.Rect({
                left: 100,
                top: 100,
                fill: 'blue',
                width: 50,
                height: 50,
            });
            canvas.add(rect);
        }
    });
</script>
