import { useState, useEffect } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { addQueryArgs } from "@wordpress/url";
import { store as noticesStore } from "@wordpress/notices";
import { store as coreStore } from "@wordpress/core-data";
import apiFetch from "@wordpress/api-fetch";
import { select, useDispatch } from "@wordpress/data";
import debounce from "debounce-promise";
import EntitySearchDropdown from "./EntitySearchDropdown";
import VideoIcon from "./VideoIcon";
import { Button, MenuItem } from "@wordpress/components";
import { capitalize } from "../../util";

const SelectMediaDropdown = ({ onSelect, value, ...props }) => {
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const [isLoading, setIsLoading] = useState(false);
  const [videoList, setVideoList] = useState([]);
  const [totalPages, setTotalPages] = useState(0);
  const { createErrorNotice } = useDispatch(noticesStore);
  const { receiveEntityRecords } = useDispatch(coreStore);

  const handleSelection = (video) => {
    if (!video) return;
    onSelect(video);
  };

  // debounce the search.
  const debounceSearch = debounce(
    () => {
      setPage(1); // reset the page.
      setVideoList(null); // clear the videos.
      doFetch(); // fetch the videos.
    },
    500,
    {
      leading: true,
    }
  );

  // when the search term changes, do a debounce search.
  useEffect(() => {
    debounceSearch(search);
  }, [search]);

  // when the page changes, fetch the videos.
  useEffect(() => {
    doFetch();
  }, [page]);

  // check if there are more pages.
  const hasMore = page < totalPages;

  // set the next page.
  const nextPage = () => {
    let newPage = page + 1;
    newPage = newPage > totalPages ? totalPages : newPage;
    setPage(newPage);
  };

  // Fetch videos from the server.
  const doFetch = async () => {
    try {
      setIsLoading(true);

      const baseURL = select(coreStore).getEntityConfig(
        "postType",
        "pp_video_block"
      ).baseURL;

      const res = await apiFetch({
        path: addQueryArgs(baseURL, {
          search,
          page,
          per_page: 10,
          _embed: 1,
        }),
        parse: false,
      });

      const videos = await res.json();

      setTotalPages(parseInt(res.headers.get("X-WP-TotalPages")));
      receiveEntityRecords("postType", "pp_video_block", videos);

      if (!search && page > 1) {
        setVideoList([...videoList, ...videos]);
      } else {
        setVideoList(videos);
      }
    } catch (error) {
      createErrorNotice(
        error?.message || __("Something went wrong", "presto-player"),
        { type: "snackbar" }
      );
    } finally {
      setIsLoading(false);
    }
  };

  // convert single value to array.
  const disabledItems = !Array.isArray(value) ? [value] : value;

  return (
    <EntitySearchDropdown
      popoverProps={{ placement: "bottom-end" }}
      isLoading={isLoading}
      options={videoList || []}
      search={search}
      onSearch={setSearch}
      onSelect={handleSelection}
      hasMore={hasMore && !search}
      onNextPage={nextPage}
      renderToggle={({ isOpen, onToggle }) => (
        <Button variant="primary" onClick={onToggle} aria-expanded={isOpen}>
          {__("Create or select media", "presto-player")}
        </Button>
      )}
      renderItem={({ item, onSelect }) => {
        const { id, title, details } = item;
        const { type } = details || {};
        const thumbnail =
          item?._embedded?.["wp:featuredmedia"]?.[0]?.source_url || "";
        return (
          <MenuItem
            icon={<VideoIcon thumbnail={thumbnail} type={type} />}
            iconPosition="left"
            suffix={
              type ? capitalize(type) : __("Choose media", "presto-player")
            }
            onClick={() => onSelect(item)}
            disabled={(disabledItems || []).includes(id)}
            key={id}
          >
            {title?.raw || __("Untitled", "presto-player")}
          </MenuItem>
        );
      }}
      {...props}
    />
  );
};

export default SelectMediaDropdown;
