document.onreadystatechange = function () {
  if (document.readyState == "complete" && typeof acf !== "undefined") {
    tinymce.PluginManager.add("limerock_shortcodes", function (editor, url) {
      editor.addButton("limerock_shortcodes", {
        title: "Shortcodes",
        type: "menubutton",
        icon: "wp_code",
        menu: [
          {
            text: "Button",
            onclick: function () {
              editor.windowManager.open({
                title: "Button parameters",
                body: [
                  {
                    type: "listbox",
                    name: "type",
                    label: "Button Type:",
                    default: "primary",
                    values: [
                      { value: "primary", text: "Primary" },
                      { value: "secondary", text: "Secondary" },
                      { value: "link", text: "Link" },
                      { value: "info", text: "Info" },
                    ],
                  },
                  {
                    type: "listbox",
                    name: "target",
                    label: "Open in:",
                    default: "_self",
                    values: [
                      { value: "_self", text: "This tab" },
                      { value: "_blank", text: "A new tab" },
                    ],
                  },
                  {
                    type: "textbox",
                    name: "text",
                    label: "Button Text:",
                  },
                  {
                    type: "textbox",
                    name: "href",
                    label: "URL:",
                  },
                  {
                    type: "textbox",
                    name: "title",
                    label: "Browser tooltip on hover:",
                  },
                  {
                    type: "textbox",
                    name: "label",
                    label: "Screenreader label:",
                  },
                ],
                onsubmit: function ({data}) {
                  editor.insertContent(
                    `[button ${
                    Object.entries(data)
                      .map(([label, value]) => `${label}=\"${value}\"`)
                      .join(' ')
                    }]`
                  );
                },
              });
            },
          },
          {
            text: "Current Year",
            onclick: function () {
              editor.insertContent("[year]");
            },
          },
        ],
      });
    });
  }
};
