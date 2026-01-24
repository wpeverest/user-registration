import React from "react";
import { __ } from "@wordpress/i18n";

const MediaButton = ({ editorId }) => {
  return (
    <button
      type="button"
      className="button insert-media add_media"
      data-editor={editorId}
    >
      <span className="wp-media-buttons-icon" />
      {__("Add Media", "user-registration")}
    </button>
  );
};

export default MediaButton;
