/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/coreui/media-cropp.js":
/*!********************************************!*\
  !*** ./resources/js/coreui/media-cropp.js ***!
  \********************************************/
/***/ (function() {

var self = this;
this.changePort = ''; // :8000

this.removeFolderModal = new coreui.Modal(document.getElementById('cropp-img-modal'));
this.cropper = null;
this.croppUrl = null;
this.croppFileId = null;
this.uploadCroppedImage = function () {
  self.cropper.getCroppedCanvas().toBlob(function (blob) {
    var formData = new FormData();
    formData.append('file', blob);
    formData.append('thisFolder', document.getElementById('this-folder-id').value);
    formData.append('id', self.croppFileId);
    axios.post('/media/file/cropp', formData).then(function (response) {
      location.reload();
    })["catch"](function (error) {
      console.log(error);
    });
  } /*, 'image/png' */);
};

this.afterShowedCroppModal = function () {
  if (self.cropper !== null) {
    self.cropper.replace(self.croppUrl);
  } else {
    var image = document.getElementById('cropp-img-img');
    self.cropper = new Cropper(image, {
      minContainerWidth: 600,
      minContainerHeight: 600
    });
  }
};
this.showCroppModal = function (data) {
  self.croppUrl = data.url;
  self.croppUrl = self.croppUrl.replace('localhost', 'localhost' + self.changePort);
  document.getElementById('cropp-img-img').setAttribute('src', self.croppUrl);
  self.removeFolderModal.show();
};
this.croppImg = function (e) {
  self.croppFileId = e.target.getAttribute('atr');
  axios.get('/media/file?id=' + self.croppFileId + '&thisFolder=' + document.getElementById('this-folder-id').value).then(function (response) {
    self.showCroppModal(response.data);
  })["catch"](function (error) {
    console.log(error);
  });
};
var croppFiles = document.getElementsByClassName("file-cropp-file");
for (var i = 0; i < croppFiles.length; i++) {
  croppFiles[i].addEventListener('click', this.croppImg);
}
document.getElementById("cropp-img-modal").addEventListener("show.coreui.modal", this.afterShowedCroppModal);
document.getElementById('cropp-img-save-button').addEventListener('click', this.uploadCroppedImage);

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module is referenced by other modules so it can't be inlined
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./resources/js/coreui/media-cropp.js"]();
/******/ 	
/******/ })()
;