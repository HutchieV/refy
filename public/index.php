<!DOCTYPE html>

<html>

  <head>
  
    <?php include '/web/refboard/includes/config.php'; ?>
    <?php include 'beta/meta.php'; ?>

    <title>RefBoard Test</title>

  </head>

  <main class="board-main">
  
    <nav class="board-nav">
    
      <div class="board-nav-e">
        <h3>RefBoard</h3>
      </div>

      <div class="board-nav-e">
        <p><strong>Controls</strong></p>
        <p><span id="clickAndDrag">Click and drag</span> to move</p>
        <p><span id="ctrl">CTRL</span>    + <span id="ctrlDrag">Drag</span> to resize</p>
        <p><span id="shift">SHIFT</span>  + <span id="shiftDrag">Drag</span> to rotate</p>
      </div>

      <div class="board-nav-e">
        <input type="file" id="fileElem" multiple accept="image/*" style="display:none">
        <a href="#" id="fileSelect">Select some files</a>
      </div>

      <div class="board-nav-e">
        <div id="fileList">
          <p>No files selected!</p>
        </div>
      </div>

      <div class="board-nav-e">
        <div id="sectorDebug">
        </div>
        <div id="resizeDebug">
        </div>
      </div>

    </nav>

    <div id="board" class="board">



    </div>
  
    <script type="text/javascript">
      var board = document.getElementById("board");
      var reader = new FileReader();

      var ctrlHeld = false, shiftHeld = false;

      document.addEventListener("keydown", function(event) {
        switch(event.which){
          case 17:
            if(!ctrlHeld)
            {
              console.log("ctrl down");
              ctrlHeld = true;
            }
            break;
          case 16:
            if(!shiftHeld)
            {
              console.log("shift down");
              shiftHeld = true;
            }
            break;
          default: 
            break;
        }
      });

      document.addEventListener("keyup", function(event) {
        switch(event.which){
          case 17:
            if(ctrlHeld)
            {
              console.log("ctrl up");
              ctrlHeld = false;
            }
            break;
          case 16:
            if(shiftHeld)
            {
              console.log("shift up");
              shiftHeld = false;
            }
            break;
          default: 
            break;
        }
      });

      const fileSelect = document.getElementById("fileSelect"),
      fileElem = document.getElementById("fileElem"),
      fileList = document.getElementById("fileList");

      fileSelect.addEventListener("click", function (e)
      {
        if (fileElem) {
          fileElem.click();
        }
        e.preventDefault(); // prevent navigation to "#"
      }, false);

      fileElem.addEventListener("change", handleFiles, false);

      function handleFiles()
      {
        if(!this.files){
          fileList.innerHTML = `<p>No files selected!</p>`
        }
        else
        {
          for (let i = 0; i < this.files.length; i++)
          {
            var img = document.createElement("img");
            img.classList.add("board-img");
            dragElement(img);
            img.height = 300;
            img.src = URL.createObjectURL(this.files[i]);
            img.onload = function() {
              URL.revokeObjectURL(this.src);
            }
            board.appendChild(img);

            const info = document.createElement("p");
            info.innerHTML = this.files[i].name + ": " + this.files[i].size + " bytes";
            fileList.appendChild(info);
          }
        }
      }

      /**
       * IMG MANIPULATION LOGIC
       */
      function dragElement(elmnt) {
        var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
        var imgX = 0, imgY = 0;
        var startHeight   = 0;
        var startRotation = 0;
        elmnt.onmousedown = dragMouseDown;

        function getCenterX() {
          return ((elmnt.clientWidth/2)+parseInt(elmnt.style.left));
        }

        function getCenterY() {
          return ((elmnt.clientHeight/2)+parseInt(elmnt.style.top));
        }

        function getCursorX(e) {
          return e.clientX;
        }

        function getCursorY(e) {
          return e.clientY;
        }

        function diff(x, y) {
          return Math.abs(x - y);
        }

        function getSectorOfCursor(imgX, imgY, curX, curY) {
          if(curY < imgY && (Math.abs(curX - imgX) < Math.abs(curY - imgY))) {
            if(curX < imgX) return 8; // Top left
            return 1; // Top right
          }
          else if(curX > imgX && (Math.abs(curY - imgY) < Math.abs(curX - imgX))) {
            if(curY < imgY) return 2; // Right top
            return 3; // Right bottom
          }
          else if(curY > imgY && (Math.abs(curX - imgX) < Math.abs(curY - imgY))) {
            if(curX > imgX) return 4; // Bottom left
            return 5; // Bottom right
          }
          else if(curX < imgX && (Math.abs(curY - imgY) < Math.abs(curX - imgX))) {
            if(curY > imgY) return 6; // Left bottom
            return 7;  // Left top
          }
        }

        function toDegrees(a) {
          return (a * (180 / Math.PI));
        }

        function getAngleFromCursor(imgX, imgY, curX, curY) {
          var a = Math.abs(curX - imgX);
          var b = Math.abs(curY - imgY);
          var c = Math.sqrt(Math.pow(a, 2) + Math.pow(b, 2));

          var o = 0;
          (a < b) ? o = a : o = b;

          var d = Math.abs(toDegrees(Math.asin(o / c)));

          switch(getSectorOfCursor(imgX, imgY, curX, curY)) {
            case 1:
              return d;
              break;
            case 2:
              return (45 - d) + 45;
              break;
            case 3:
              return d + 90;
              break;
            case 4:
              return (45 - d) + 135;
              break;
            case 5:
              return d + 180;
              break;
            case 6:
              return (45 - d) + 225;
              break;
            case 7:
              return d + 270;
              break;
            case 8:
              return (45 - d) + 315;
              break;
            default: 
              return undefined;
              break;
          }
        }

        function dragMouseDown(e) {
          imgX = getCenterX();
          imgY = getCenterY();

          document.getElementById("sectorDebug").innerHTML  = `<p><strong>Stats at start</strong></p>`;
          document.getElementById("sectorDebug").innerHTML += `<p>Image center: ` + getCenterX() + `, ` + getCenterY() + `</p>`;
          document.getElementById("sectorDebug").innerHTML += `<p>Cursor pos: ` + getCursorX(e) + `, ` + getCursorY(e) + `</p>`;
          document.getElementById("sectorDebug").innerHTML += `<p>Sector: ` + getSectorOfCursor(imgX, imgY, getCursorX(e), getCursorY(e)) + `</p>`;
          document.getElementById("sectorDebug").innerHTML += `<p>Angle: ` + getAngleFromCursor(imgX, imgY, getCursorX(e), getCursorY(e)) + `</p>`;

          startRotation = getAngleFromCursor(getCenterX(), getCenterY(), getCursorX(e), getCursorY(e));

          e = e || window.event;
          e.preventDefault();

          // Get the mouse cursor position at startup
          pos3 = e.clientX;
          pos4 = e.clientY;
          startHeight = elmnt.height;

          if(ctrlHeld)
          {
            document.onmouseup = closeElementResize;
            document.onmousemove = elementResize;
          }
          else if(shiftHeld)
          {
            document.onmouseup = closeElementRotate;
            document.onmousemove = elementRotate;
          }
          else
          {
            document.onmouseup = closeDragElement;
            document.onmousemove = elementDrag;
          }
        }

        /**
         * ROTATING LOGIC
         */
        function elementRotate(e) {
          e = e || window.event;
          e.preventDefault();
          pos1 = pos3 - e.clientX;

          var newRot = -(startRotation - getAngleFromCursor(imgX, imgY, e.clientX, e.clientY));
          document.getElementById("resizeDebug").innerHTML = `<p>New rotation: `+ newRot +`</p>`;

          elmnt.style.transform = `rotate(`+newRot+`deg)`;
        }

        function closeElementRotate() {
          // Stop resizing when the mouse button is released:
          document.onmouseup    = null;
          document.onmousemove  = null;
        }

        /**
         * RESIZING LOGIC
         */
        function elementResize(e) {
          e = e || window.event;
          e.preventDefault();
          // Calculate the distance from original position:
          pos1 = pos3 - e.clientX;
          document.getElementById("resizeDebug").innerHTML  = `<p>Resizing by: `+((1+pos1/100)/2)+`</p>`;
          document.getElementById("resizeDebug").innerHTML += `<p>New height: `+startHeight * ((1+pos1/100)/2)+`</p>`;

          // elmnt.height = (startHeight * ((1+pos1/100)/2));
        }

        function closeElementResize() {
          // Stop resizing when the mouse button is released:
          document.onmouseup    = null;
          document.onmousemove  = null;
        }

        /**
         * DRAGGING LOGIC
         */
        function elementDrag(e) {
          e = e || window.event;
          e.preventDefault();
          // Calculate the new cursor position:
          pos1 = pos3 - e.clientX;
          pos2 = pos4 - e.clientY;
          pos3 = e.clientX;
          pos4 = e.clientY;
          // Set the element's new position:
          elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
          elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
        }

        function closeDragElement() {
          // Stop moving when mouse button is released:
          document.onmouseup    = null;
          document.onmousemove  = null;
        }
      }
    </script>  

  </main>

</html>