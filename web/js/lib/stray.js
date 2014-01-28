function StrayJs() {

  var _this = this;
  this.components = { };
  this.events = { };
  this.messages = { };

  /*
   * Components
   */

  this.componentRegister = function(object) {
    if ("object" !== typeof object) {
      console.log("Parameter must be an object.");
    }
    else if (false === object.hasOwnProperty('name')) {
      console.log("Component must have a 'name' property.");
    }
    else if (this.components[object.name]) {
      console.log("There's realdy a component with this name.");
    }
    else {
      this.components[object.name] = object;
    }
  };

  /*
   * Messages
   */

  this.messagePublish = function(message, data) {
    message = String(message);
    data = ("object" === typeof data ? data : { });
    if (this.messages[message]) {
      for (var i = 0; i < this.messages[message].length; i++) {
        var object = this.components[this.messages[message][i].component];
        try {
          object[this.messages[message][i].method](data);
        }
        catch(e) {
          console.error(e);
        }
        if ("undefined" === typeof this.messages[message]) {
          break;
        }
      }
    }
  };

  this.messageSubscribe = function(message, component, method) {
    if ("object" !== typeof component) {
      console.log("Second parameter must be an object.");
    }
    else if (false === component.hasOwnProperty('name')) {
      console.log("Component must have a 'name' property.");
    }
    else {
      message = String(message);
      component = String(component.name);
      method = String(method);
      if (!this.components[component]) {
        console.log("Can't find component with the name '" + component + "'.");
      }
      else {
        if (!this.messages[message]) {
          this.messages[message] = new Array();
        }
        var valid = true;
        for (var i = 0; i < this.messages[message].length; i++) {
          if (this.messages[message][i].component === component) {
            valid = false;
            console.log("This component has already registered this message.");
            break;
          }
        }
        if (true === valid) {
          this.messages[message].push({
            component: component,
            method: method
          });
        }
      }
    }
  };

  this.messageUnsubscribe = function(message, component) {
    if ("object" !== typeof component) {
      console.log("Second parameter must be an object.");
    }
    else if (false === component.hasOwnProperty('name')) {
      console.log("Component must have a 'name' property.");
    }
    else {
      message = String(selector);
      component = String(component.name);
      if (!this.components[component]) {
        console.log("Can't find component with the name '" + component + "'.");
      }
      else if (this.messages[message]) {
        for (var i = 0; this.messages[message].length; i++) {
          if (this.messages[message][i].component === component) {
            delete this.messages[message][i];
            if (1 === this.messages[message].length) {
              delete this.messages[message];
            }
            break;
          }
        }
      }
    }
  };

};

window.stray = new StrayJs();

if ("function" === typeof define && define.amd) {
  define("stray", ["jquery"], function() {
    return window.stray;
  });
}
