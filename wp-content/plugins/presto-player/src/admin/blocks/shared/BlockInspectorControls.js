import { PanelBody } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { useSelect } from "@wordpress/data";
import { store as coreStore } from "@wordpress/core-data";
import VideoBranding from "@/admin/blocks/shared/branding";
import VideoChapters from "@/admin/blocks/shared/chapters";
import ProBadge from "@/admin/blocks/shared/components/ProBadge";
import VideoOverlays from "@/admin/blocks/shared/overlays";
import VideoPresets from "@/admin/blocks/shared/presets";
import VideoSettings from "@/admin/blocks/shared/settings";

export default function ({ attributes, setAttributes }) {
  const userCanReadSettings = useSelect((select) =>
    select(coreStore).canUser("read", "settings")
  );

  return (
    <>
      <PanelBody
        title={
          <>
            {__("Chapters", "presto-player")}{" "}
            {!prestoPlayer?.isPremium && <ProBadge />}
          </>
        }
        initialOpen={prestoPlayer?.isPremium}
      >
        <VideoChapters setAttributes={setAttributes} attributes={attributes} />
      </PanelBody>

      <PanelBody
        title={
          <>
            {__("Overlays", "presto-player")}{" "}
            {!prestoPlayer?.isPremium && <ProBadge />}
          </>
        }
        initialOpen={prestoPlayer?.isPremium}
      >
        <VideoOverlays setAttributes={setAttributes} attributes={attributes} />
      </PanelBody>

      <PanelBody title={__("Video settings", "presto-player")}>
        <VideoSettings setAttributes={setAttributes} attributes={attributes} />
      </PanelBody>

      <PanelBody title={__("Video Preset", "presto-player")}>
        <VideoPresets setAttributes={setAttributes} attributes={attributes} />
      </PanelBody>

      {!!userCanReadSettings && (
        <PanelBody
          title={__("Global Player Branding", "presto-player")}
          initialOpen={false}
        >
          <VideoBranding
            setAttributes={setAttributes}
            attributes={attributes}
          />
        </PanelBody>
      )}
    </>
  );
}
