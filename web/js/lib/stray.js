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
   * Events
   */

  this.eventCallback = function(event) {
    var element = this;
    var selector = event.data.selector;
    if (_this.events[selector] && _this.events[selector][event.type]) {
      for (var i = 0; i < _this.events[selector][event.type].length; i++) {
        var object = _this.components[_this.events[selector][event.type][i].component];
        try {
          object[_this.events[selector][event.type][i].method](element, selector.replace('§', ' '), event.type);
        }
        catch(e) {
          console.error(e);
        }
        if ("undefined" === typeof _this.events[selector] || "undefined" === typeof _this.events[selector][event.type]) {
          break;
        }
      }
    }
    return false;
  };

  this.eventSubscribe = function(selector, event, component, method) {
    if ("object" !== typeof component) {
      console.log("Third parameter must be an object.");
    }
    else if (false === component.hasOwnProperty('name')) {
      console.log("Component must have a 'name' property.");
    }
    else {
      originalSelector = String(selector);
      selector = originalSelector.replace(" ", "§");
      event = String(event);
      component = Object(component.name);
      method = String(method);
      if (!this.components[component]) {
        console.log("Can't find component with the name '" + component + "'.");
      }
      else {
        if (!this.events[selector]) {
          this.events[selector] = { };
        }
        if (!this.events[selector][event]) {
          this.events[selector][event] = new Array();
        }
        var valid = true;
        for (var i = 0; this.events[selector][event].length; i++) {
          if (this.events[selector][event][i].component === component) {
            valid = false;
            console.log("This component has already registered this event on this selector.");
            break;
          }
        }
        if (true === valid) {
          this.events[selector][event].push({
            component: component,
            method: method
          });
          $(document).on(event, originalSelector, { selector: selector }, this.eventCallback);
        }
      }
    }
  };

  this.eventUnsubscribe = function(selector, event, component) {
    if ("object" !== typeof component) {
      console.log("Third parameter must be an object.");
    }
    else if (false === component.hasOwnProperty('name')) {
      console.log("Component must have a 'name' property.");
    }
    else {
      originalSelector = String(selector);
      selector = originalSelector.replace(" ", "§");
      event = String(event);
      component = String(component.name);
      if (!this.components[component]) {
        console.log("Can't find component with the name '" + component + "'.");
      }
      else if (this.events[selector]) {
        if (this.events[selector][event]) {
          for (var i = 0; this.events[selector][event].length; i++) {
            if (this.events[selector][event][i].component === component) {
              delete this.events[selector][event][i];
              if (1 === this.events[selector][event].length) {
                $(document).off(event, originalSelector, this.eventCallback);
                delete this.events[selector][event];
                if (1 === this.events[selector].length) {
                  delete this.events[selector];
                }
              }
              break;
            }
          }
        }
      }
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
