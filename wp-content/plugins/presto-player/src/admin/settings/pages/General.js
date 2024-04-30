import { __ } from "@wordpress/i18n";
import { useEntityProp, store as coreStore } from "@wordpress/core-data";
import { useSelect } from "@wordpress/data";
import {
  ColorPicker,
  ComboboxControl,
  ToggleControl,
  RangeControl,
  Spinner,
} from "@wordpress/components";
import Disabled from "../components/Disabled";
import Group from "../components/Group";
import Media from "../components/Media";
import Page from "../components/Page";
import CodeMirror from "../components/CodeMirror";

export default () => {
  const disabled = () => {
    if (prestoPlayer?.isPremium) {
      return false;
    }
    return {
      title: __("Pro Feature", "presto-player"),
      heading: __("Unlock Presto Player Pro", "presto-player"),
      message: __(
        "Get this feature and more with the Pro version of Presto Player!",
        "presto-player"
      ),
      link: "https://prestoplayer.com",
    };
  };

  const {
    presets,
    loadingPresets,
    audioPresets,
    loadingAudioPresets,
  } = useSelect((select) => {
    const presetArgs = ["presto-player", "preset"];
    const audioPresetArgs = ["presto-player", "audio-preset"];
    return {
      presets: select(coreStore).getEntityRecords(...presetArgs),
      loadingPresets: select(coreStore).isResolving(
        "getEntityRecords",
        presetArgs
      ),
      audioPresets: select(coreStore).getEntityRecords(...audioPresetArgs),
      loadingAudioPresets: select(coreStore).isResolving(
        "getEntityRecords",
        audioPresetArgs
      ),
    };
  }, []);

  const [presetSettings, setPresetSettings] = useEntityProp(
    "root",
    "site",
    "presto_player_presets"
  );
  const updatePresetSettings = (data) => {
    setPresetSettings({
      ...(presetSettings || {}),
      ...data,
    });
  };

  const [audioPresetSettings, setAudioPresetSettings] = useEntityProp(
    "root",
    "site",
    "presto_player_audio_presets"
  );
  const updateAudioPresetSettings = (data) => {
    setAudioPresetSettings({
      ...(audioPresetSettings || {}),
      ...data,
    });
  };

  const [analytics, setAnalytics] = useEntityProp(
    "root",
    "site",
    "presto_player_analytics"
  );
  const updateAnalytics = (data) => {
    setAnalytics({
      ...(analytics || {}),
      ...data,
    });
  };

  const [branding, setBranding] = useEntityProp(
    "root",
    "site",
    "presto_player_branding"
  );
  const updateBranding = (data) => {
    setBranding({
      ...(branding || {}),
      ...data,
    });
  };

  const [uninstall, setUninstall] = useEntityProp(
    "root",
    "site",
    "presto_player_uninstall"
  );
  const updateUninstall = (data) => {
    setUninstall({
      ...(uninstall || {}),
      ...data,
    });
  };

  return (
    <Page
      title={__("General", "presto-player")}
      description={__(
        "Branding, analytics and uninstall data.",
        "presto-player"
      )}
    >
      <Group
        title={__("Branding", "presto-player")}
        description={__("Global player branding options", "presto-player")}
      >
        <Disabled disabled={disabled()}>
          <Media
            className={"presto-player__setting--logo"}
            label={
              <>
                {__("Logo", "presto-player")}{" "}
                {disabled() && (
                  <span className="presto-options__pro-badge">
                    {__("Pro", "presto-player")}
                  </span>
                )}
              </>
            }
            onSelect={(image) => updateBranding({ logo: image?.url })}
            maxWidth={branding?.logo_width || 150}
            value={branding?.logo}
          />

          <div style={{ maxWidth: "500px" }}>
            <RangeControl
              className={"presto-player__setting--logo-width"}
              label={__("Logo Max Width", "presto-player")}
              value={branding?.logo_width || 150}
              onChange={(logo_width) => updateBranding({ logo_width })}
              min={1}
              max={400}
            />
          </div>
        </Disabled>
        <ColorPicker
          className={"presto-player__setting--brand-color"}
          onChangeComplete={(value) => updateBranding({ color: value.hex })}
          color={branding?.color}
        />
      </Group>
      <Group
        title={__("Analytics", "presto-player")}
        disabled={disabled()}
        description={__(
          "Analytics settings for media plays, watch times and more.",
          "presto-player"
        )}
      >
        <div>
          <ToggleControl
            className={"presto-player__setting--analytics-enable"}
            label={__("Enable", "presto-player")}
            help={__("Enable view analytics for your media", "presto-player")}
            checked={analytics?.enable}
            onChange={(enable) => updateAnalytics({ enable })}
          />

          {!!analytics?.enable && (
            <ToggleControl
              label={__("Auto-Purge Data (recommended)")}
              help={__(
                "Automatically purge data older than 90 days.",
                "presto-player"
              )}
              className={"presto-player__setting--analytics-enable"}
              checked={
                analytics?.purge_data !== undefined
                  ? analytics?.purge_data
                  : true
              }
              onChange={(purge_data) => updateAnalytics({ purge_data })}
            />
          )}
        </div>
      </Group>
      <Group
        title={__("Presets", "presto-player")}
        disabled={disabled()}
        description={__("Media presets settings.", "presto-player")}
      >
        {!!loadingPresets ? (
          <Spinner />
        ) : (
          <ComboboxControl
            label={__("Select default preset for video.", "presto-player")}
            value={presetSettings?.default_player_preset}
            options={(presets || []).map((preset) => {
              return {
                value: preset?.id,
                label: preset?.name,
              };
            })}
            onChange={(default_player_preset) =>
              updatePresetSettings({
                default_player_preset: default_player_preset || 1,
              })
            }
          />
        )}

        {!!loadingAudioPresets ? (
          <Spinner />
        ) : (
          <ComboboxControl
            label={__("Select default preset for audio.", "presto-player")}
            value={audioPresetSettings?.default_player_preset}
            options={(audioPresets || []).map((preset) => {
              return {
                value: preset?.id,
                label: preset?.name,
              };
            })}
            onChange={(default_player_preset) =>
              updateAudioPresetSettings({
                default_player_preset: default_player_preset || 1,
              })
            }
          />
        )}
      </Group>
      <Group
        disabled={disabled()}
        title={__("Custom CSS", "presto-player")}
        description={__(
          "Quickly add custom css to the player web component.",
          "presto-player"
        )}
      >
        <CodeMirror
          disabled={!prestoPlayer?.isPremium}
          option={{ id: "player_css" }}
          value={branding?.player_css}
          onChange={(player_css) => updateBranding({ player_css })}
        />
      </Group>
      <Group
        title={__("Uninstall Options", "presto-player")}
        description={__(
          "Options to remove data on uninstall.",
          "presto-player"
        )}
      >
        <ToggleControl
          label={__("Remove data on uninstall")}
          help={__("This removes all data on uninstall.", "presto-player")}
          className="presto-player__setting--uninstall"
          checked={uninstall?.uninstall_data}
          onChange={(uninstall_data) => {
            if (uninstall_data) {
              const r = confirm(
                __(
                  "Caution: Data Loss. Are you sure you want to remove all the data from this plugin? This is irreversible!",
                  "presto-player"
                )
              );
              if (!r) return;
            }
            updateUninstall({ uninstall_data });
          }}
        />
      </Group>
    </Page>
  );
};
